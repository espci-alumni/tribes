<?php

class agent_user_edit extends agent_pForm
{
    const

    PHOTO_WIDTH = 128,
    PHOTO_HEIGHT = 128;

    protected

    $requiredAuth = true,
    $loginField,
    $photoField,
    $cvField,

    $contact_id,
    $confirmed = false,
    $contact = false,
    $email = false,
    $adresse = false,
    $activite = false,

    $emails,
    $adresses,
    $activites,
    $deletedEmail = array();


    function control()
    {
        parent::control();

        empty($this->contact_id) && $this->contact_id = $this->connected_id;

        $this->contact = new tribes_contact($this->contact_id, $this->confirmed);

        $this->data = (array) $this->data;
        $this->data += $this->contact->fetchRow('contact_id, login, contact_confirmed, admin_confirmed, contact_modified, photo_token, cv_token, contact_data, acces');

        $this->email = new tribes_email($this->contact_id, $this->confirmed);
        $this->adresse = new tribes_adresse ($this->contact_id, $this->confirmed);
        $this->activite = new tribes_activite($this->contact_id, $this->confirmed);

        $this->data = (object) $this->data;
        $this->data->contact_id =& $this->contact_id;
    }

    function compose($o)
    {
        $o->contact_id = $this->contact_id;
        $o->login = $this->data->login;

        $o->is_admin_confirmed = $this->data->admin_confirmed > $this->data->contact_modified;
        $o->connected_is_admin = $this->connected_is_admin;

        $o->emails =& $this->emails;
        $o->adresses =& $this->adresses;
        $o->activites =& $this->activites;

        return parent::compose($o);
    }

    protected function composeForm($o, $f, $send)
    {
        $o = $this->composeLogin($o, $f, $send);
        $o = $this->composeContact($o, $f, $send);
        $o = $this->composeEmail($o, $f, $send);
        $o = $this->composeAdresse($o, $f, $send);
        $o = $this->composeActivite($o, $f, $send);
        $o = $this->composeNewPassword($o, $f, $send);
        $o = $this->composePassword($o, $f, $send);

        return $o;
    }

    protected function formIsOk($f)
    {
        if ($this->isLoginCollision($this->contact_id))
        {
            return false;
        }

        if ($e = $f->getElement('cur_pwd'))
        {
            if (!Patchwork::matchSaltedHash($e->getValue(), SESSION::get('saltedPassword')))
            {
                $e->setError('Mot de passe incorrect');
                return false;
            }
        }

        if ($e = $f->getElement('con_pwd'))
        {
            if ($f->getElement('password')->getValue() !== $e->getValue())
            {
                $e->setError('Confirmation échouée');
                return false;
            }
        }

        if (isset($this->emails) && !$this->emails->adminMode)
        {
            if (!$this->hasActiveItem($this->emails))
            {
                if ($e = $f->getElement('email_add'))
                {
                    $e->setError('Veuillez sélectionner au moins une adresse email de correspondance');
                    return false;
                }
                else E('email_add non défini ?!');
            }
        }

        if (isset($this->adresses) && !$this->adresses->adminMode)
        {
            if (!$this->hasActiveItem($this->adresses))
            {
                if ($e = $f->getElement('adresse_add'))
                {
                    $e->setError('Veuillez sélectionner au moins une adresse de correspondance');
                    return false;
                }
                else E('adresse_add non défini ?!');
            }
        }

        return true;
    }

    protected function composePassword($o, $f, $send)
    {
        $f->add('password', 'cur_pwd', array('isdata' => false));
        $send->attach('cur_pwd', 'Veuillez saisir votre mot de passe actuel', '');
        return $o;
    }

    protected function composeNewPassword($o, $f, $send)
    {
        $f->add('password', 'password');
        $f->add('password', 'con_pwd', array('isdata' => false));

        $send->attach(
            'password', '', '',
            'con_pwd', '', ''
        );

        return $o;
    }

    protected function composeContact($o, $f, $send)
    {
        $f->add('check', 'sexe', array('item' => array(
            'F' => 'Mme, Mlle',
            'M' => 'M.'
        )));

        $f->add('name', 'prenom_usuel');
        $f->add('name', 'nom_usuel');
        $f->add('name', 'nom_etudiant');
        $f->add('name', 'prenom_civil');
        $f->add('name', 'nom_civil');
        $f->add('date', 'date_naissance');
        $f->add('email', 'conjoint_email');

        $send->attach(
            'sexe', "Veuillez renseigner le champs Mme Mlle M.", '',
            'prenom_usuel', "Veuillez renseigner le prénom usuel", '',
            'nom_usuel', "Veuillez renseigner le nom usuel", '',
            'nom_etudiant', '', '',
            'prenom_civil', "Veuillez renseigner le prenom civil", '',
            'nom_civil', "Veuillez renseigner le nom de naissance", '',
            'date_naissance', '', '',
            'conjoint_email', '', 'Veuillez renseigner une adresse email valide'
        );

        $o = $this->composePhoto($o, $f, $send);
        $o = $this->composeCv($o, $f, $send);

        return $o;
    }

    protected function composeLogin($o, $f, $send)
    {
        if (!empty($this->data->login))
        {
            $this->loginField = $f->add('text', 'login', '[a-z]+(?:-?[a-z]+)+\.[a-z]+(?:-?[a-z]+)+');
            $send->attach('login', 'Veuillez saisir un identifiant', "Seul le format prenom.nom sans caractères spéciaux est autorisé");
        }

        return $o;
    }

    protected function composeEmail($o, $f, $send)
    {
        $this->emails = new loop_edit_contact_email($f, $this->contact_id, $send);

        $sql = "SELECT alias
                FROM contact_alias
                WHERE contact_id={$this->contact_id}
                    AND hidden=0
                ORDER BY alias";
        $o->aliases = new loop_sql($sql);

        return $o;
    }

    protected function composePhoto($o, $f, $send)
    {
        $file = explode('.', $this->data->photo_token) + array(1 => 'jpg', 'jpg');
        $o->photo_token = implode('.', $file);

        $file[0] = patchworkPath('data/photo/') . $file[0];
        $o->hasPhoto = file_exists("{$file[0]}.{$file[1]}") || file_exists("{$file[0]}.{$file[2]}~");

        if ($o->hasPhoto)
        {
            $f->add('check', 'del_photo', array(
                'item' => array(1 => 'Supprimer cette photo'),
                'multiple' => true,
                'isdata' => true,
            ));

            $send->attach('del_photo', '', '');
        }

        $this->photoField = $f->add('file', 'photo', array('valid' => 'image', null, array('jpg','gif','png')));

        $send->attach('photo', '', "Format d'image non valide");

        return $o;
    }

    protected function composeCv($o, $f, $send)
    {
        $file = explode('.', $this->data->cv_token) + array(1 => 'pdf', 'pdf');
        $o->cv_token = implode('.', $file);

        $file[0] = patchworkPath('data/cv/') . $file[0];
        $o->hasCv = file_exists("{$file[0]}.{$file[1]}") || file_exists("{$file[0]}.{$file[2]}~");

        if ($o->hasCv)
        {
            $f->add('check', 'del_cv', array(
                'item' => array(1 => 'Supprimer ce CV'),
                'multiple' => true,
                'isdata' => true,
            ));

            $send->attach('del_cv', '', '');
        }

        $this->cvField = $f->add('file', 'cv');

        $send->attach('cv', '', 'Type de fichier non valide');

        return $o;
    }

    protected function composeAdresse($o, $f, $send, $new = false)
    {
        $this->adresses = new loop_edit_contact_adresse($f, $this->contact_id, $send, $new);

        return $o;
    }

    protected function composeActivite($o, $f, $send, $new = false)
    {
        $sql = "SELECT `value` AS K, `group` AS G, `value` AS V
                FROM item_lists
                WHERE type='contact/statut'
                ORDER BY sort_key, `group`, `value`";
        $f->add('select', 'statut_activite', array(
            'firstItem' => '- Choisir dans la liste -',
            'sql' => $sql,
        ));

        $send->attach(
            'statut_activite', $this->connected_is_admin ? '' : 'Veuillez renseigner votre statut principal actuel', ''
        );

        $this->activites = new loop_edit_contact_activite($f, $this->contact_id, $send, $new);

        return $o;
    }


    protected function save($data)
    {
        $this->saveContact($data);
        $this->saveEmail($data);
        $this->saveAdresse($data);
        return $this->saveActivite($data);
    }

    protected function saveContact($data)
    {
        isset($data['nom_civil'], $data['nom_etudiant'])
            && empty($data['nom_etudiant'])
            && $data['nom_etudiant'] = $data['nom_civil'];

        $this->savePhoto($data);
        $this->saveCv($data);

        $this->contact->save($data);
    }

    protected function saveEmail($data)
    {
        $counter = 0;

        while ($b = $this->emails->loop())
        {
            if (empty($b->deleted) && $b->f_email->getStatus())
            {
                $a = array(
                    'email' => $b->f_email->getDbValue(),
                );

                if ('' !== implode('', $a))
                {
                    $a += array(
                        'is_active' => $b->f_is_active->getValue() ? 1 : 0,
                        'contact_id' => $this->contact_id,
                        'sort_key' => ++$counter,
                    );

                    $this->email->save($a, null, $b->id);
                }
                else $b->deleted = true;
            }

            if (!empty($b->deleted) && $b->id)
            {
                $this->deletedEmail[$b->email] = 1;
                $this->email->delete($b->id);
            }
        }
    }

    protected function saveAdresse($data)
    {
        $counter = 0;

        while ($b = $this->adresses->loop())
        {
            if (isset($b->f_decision) ? $b->f_decision->getValue() : empty($b->deleted))
            {
                $a = $b->f_description->getData() + array(
                    'description' => $b->f_description->getDbValue(),
                    'ville' => $b->f_ville->getDbValue(),
                );

                if ('' !== $a['email_list'])
                {
                    preg_match_all("'" . FILTER::EMAIL_RX . "'", $a['email_list'], $email);

                    $a['email_list'] = '';

                    foreach ($email[0] as $email)
                    {
                        if (isset($this->deletedEmail[strtolower($email)])) continue;

                        $a['email_list'] .= $email . "\n";
                        $this->email->save(array('email' => $email));
                    }
                }

                if ('' !== implode('', $a))
                {
                    $a += array(
                        'is_active' => $b->f_is_active->getValue() ? 1 : 0,
                        'contact_id' => $this->contact_id,
                        'sort_key' => ++$counter,
                    );

                    $this->adresse->save($a, null, $b->id);
                }
                else $b->deleted = true;
            }

            if (!empty($b->deleted) && $b->id)
            {
                $this->adresse->delete($b->id);
            }
        }
    }

    protected function saveActivite($data)
    {
        if (empty($data['nom_civil']))
        {
            $this->contact->save($data);
        }

        $counter = 0;
        $i = 0;
        $adresse_id = array();

        $db = DB();

        $sql = "DELETE FROM contact_adresse
                WHERE contact_id={$this->contact_id}
                    AND origine='contact/{$this->connected_id}'
                    AND NOT contact_data
                    AND NOT admin_confirmed
                    AND NOT contact_confirmed";
        $db->exec($sql);

        $has_new_adresse = false;

        while ($b = $this->activites->loop())
        {
            ++$i;

            if (isset($b->f_decision) ? $b->f_decision->getValue() : empty($b->deleted))
            {
                $a = $b->f_organisation->getData() + array(
                    'organisation' => $b->f_organisation->getDbValue(),
                );

                if (isset($b->f_adresse_id))
                {
                    $adresse_id[$i] = $b->f_adresse_id->getDbValue();

                    if ('new' === $adresse_id[$i])
                    {
                        $has_new_adresse = true;

                        $sql = "INSERT INTO contact_adresse (contact_id, origine)
                                VALUES ({$this->contact_id}, 'contact/{$this->connected_id}')";
                        $db->exec($sql);

                        $adresse_id[$i] = $db->lastInsertId();
                    }
                    else if ($adresse_id[$i] < 0)
                    {
                        $adresse_id[$i] = $adresse_id[-$adresse_id[$i]];
                    }

                    $a['adresse_id'] = $adresse_id[$i];
                }

                if ('' !== implode('', $a))
                {
                    $a += array(
                        'contact_id' => $this->contact_id,
                        'sort_key' => ++$counter,
                    );

                    $this->activite->save($a, null, $b->id);
                }
                else $b->deleted = true;
            }

            if (!empty($b->deleted) && $b->id)
            {
                $this->activite->delete($b->id);
            }
        }

        return $has_new_adresse ? 'user/edit/adresse/activite' : '';
    }

    protected function savePhoto(&$data)
    {
        $token = explode('.', $this->data->photo_token) + array(1 => 'jpg', 'jpg');

        if (!empty($data['del_photo']))
        {
            $file = patchworkPath('data/photo/') . "{$token[0]}.{$token[2]}~";

            if (file_exists($file)) unlink($file);
            else $this->data->photo_token = '';
        }

        if (!$this->data->photo_token)
        {
            $token[0] = $this->data->photo_token = $data['photo_token'] = Patchwork::strongid(8);
        }

        if (isset($this->photoField) && $this->photoField->getStatus())
        {
            $file = $this->photoField->getValue();

            $th_w = self::PHOTO_WIDTH;
            $th_h = self::PHOTO_HEIGHT;

            list($src_w, $src_h, $src_type) = $file['info'];

            switch ($src_type)
            {
                case 'gif': $src_img = imagecreatefromgif($file['tmp_name']); break;
                case 'jpg': $src_img = imagecreatefromjpeg($file['tmp_name']); break;
                case 'png': $src_img = imagecreatefrompng($file['tmp_name']); break;
                default : return;
            }

            if ($src_w > $src_h) $th_h *= $src_h / $src_w;
            else if ($src_w < $src_h) $th_w *= $src_w / $src_h;

            $th_img = imagecreatetruecolor($th_w, $th_h);
            $bgcolor = imagecolorallocate($th_img, 255, 255, 255);
            imagefilledrectangle($th_img, 0, 0, $th_w, $th_h, $bgcolor);
            imagecopyresampled($th_img, $src_img, 0, 0, 0, 0, $th_w, $th_h, $src_w, $src_h);

            $file = patchworkPath('data/photo/') . $token[0];

            @unlink("{$file}.{$token[2]}~");
            'jpg' !== $token[2] && @unlink($file . ".jpg~");
            $token[2] = 'jpg';
            imagejpeg($th_img, $file . ".{$token[2]}~", 90);

            $this->confirmed || $this->contact->updateContactModified($this->contact_id);
        }

        if ($this->confirmed)
        {
            $file = array(patchworkPath('data/photo/'), $token[0], ".{$token[2]}");

            $token = Patchwork::strongid(8);

            if (@rename($file[0] . $file[1] . $file[2] . '~', $file[0] . $token . $file[2]))
            {
                $token .= $file[2];

                notification::send('user/photo', array(
                    'contact_id' => $this->contact_id,
                    'photo_token' => $token,
                ));

                $this->data->photo_token = $data['photo_token'] = $token;
            }
        }
    }

    protected function saveCv(&$data)
    {
        $token = explode('.', $this->data->cv_token) + array(1 => 'pdf', 'pdf');
        $cv_text = '';

        if (!empty($data['del_cv']))
        {
            $file = patchworkPath('data/cv/') . "{$token[0]}.{$token[2]}~";

            if (file_exists($file)) unlink($file);
            else $this->data->cv_token = '';

            if (!empty($this->data->cv_text))
            {
                $data['cv_text'] = '';
                $this->contact->updateContactModified($this->contact_id);
            }
        }

        if (!$this->data->cv_token)
        {
            $token[0] = $this->data->cv_token = $data['cv_token'] = Patchwork::strongid(8);
        }

        if (isset($this->cvField) && $this->cvField->getStatus())
        {
            if ($file = $this->cvField->getValue())
            {
                $ext = array(patchworkPath('data/cv/') . $token[0], '');

                foreach (array('pdf', 'doc') as $ext[1])
                {
                    $cv_text = 'converter_txt_' . $ext[1];
                    $cv_text = new $cv_text;
                    if ($cv_text = $cv_text->convertFile($file['tmp_name']))
                    {
                        @unlink("{$ext[0]}.{$token[2]}~");
                        $ext[1] !== $token[2] && @unlink("{$ext[0]}.{$ext[1]}~");
                        $token[2] = $ext[1];
                        $this->data->cv_token = $data['cv_token'] = implode('.', $token);
                        move_uploaded_file($file['tmp_name'], "{$ext[0]}.{$token[2]}~");

                        $this->confirmed || $this->contact->updateContactModified($this->contact_id);
                        break;
                    }
                }

                unset($ext);
            }
        }

        if ($this->confirmed)
        {
            $file = array(patchworkPath('data/cv/'), $token[0], ".{$token[2]}");

            $token = Patchwork::strongid(8);

            if (@rename($file[0] . $file[1] . $file[2] . '~', $file[0] . $token . $file[2]))
            {
                if (!$cv_text)
                {
                    $cv_text = 'converter_txt_' . substr($file[2], 1);
                    $cv_text = new $cv_text;
                    $cv_text = $cv_text->convertFile($file[0] . $token . $file[2]);
                }

                $token .= $file[2];

                notification::send('user/cv', array(
                    'contact_id' => $this->contact_id,
                    'cv_token' => $token,
                ));

                $this->data->cv_token = $data['cv_token'] = $token;
                $data['cv_text'] = $cv_text;
            }
        }
    }

    protected function isLoginCollision($contact_id)
    {
        if (empty($this->loginField)) return false;

        $sql = str_replace('-', '', $this->loginField->getValue());
        $sql = "SELECT 1
                FROM contact_alias
                WHERE alias='{$sql}'
                    AND contact_id!={$contact_id}";
        if (DB()->queryOne($sql))
        {
            $this->loginField->setError('Identifiant déjà utilisé');
            return true;
        }

        return false;
    }

    protected function hasActiveItem(&$loop)
    {
        $has_active_data = false;
        $data = array();

        while ($e = $loop->loop())
        {
            $data[] = $e;
            if (empty($e->deleted) && $e->f_is_active->getValue()) $has_active_data = true;
        }

        $loop = new loop_array($data, 'filter_rawArray');

        return $has_active_data;
    }
}
