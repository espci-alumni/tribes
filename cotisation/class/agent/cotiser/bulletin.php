<?php

// TODO: cas mot de passe perdu
// Dans le cas où cette page s'affiche suite à un passage par registration
// alors que le contact est déjà accepté, il faudrait ajouter une question
// dans le formulaire "Envoyer un nouveau mot de passe ? O/N".
// Et envoyer un lien de récuparation de mot de passe, le cas échéant.
// cf. notification::send('user/password/request',...) dans agent/registration/collision

class agent_cotiser_bulletin extends agent_user_edit
{
    protected static

    $soutien = array(
         50 => '50€',
        100 => '100€',
        200 => '200€',
    );

    protected $requiredAuth = false; // Assume own acces control


    function control()
    {
        $this->contact_id = SESSION::get('cotisation_contact_id');
        $this->contact_id || $this->contact_id = tribes::getConnectedId();
        $this->contact_id || Patchwork::redirect('cotiser');
        $this->connected_id = $this->contact_id;

        parent::control();

        if ($k = SESSION::get('cotisation_bulletin'))
            foreach ($k as $k => $v)
                $this->data->$k = $v;
    }

    function compose($o)
    {
        $sql = "SELECT email
                FROM contact_email
                WHERE contact_id={$this->contact_id}
                    AND is_obsolete<=0
                    AND contact_confirmed
                ORDER BY is_active DESC, is_obsolete DESC
                LIMIT 1";

        $sql = "SELECT
                    contact_id,
                    sexe,
                    nom_usuel AS nom,
                    prenom_usuel AS prenom,
                    IF(login!='',CONCAT(login,'{$CONFIG['tribes.emailDomain']}'),({$sql})) AS email,
                    IF (cotisation_expires+INTERVAL 1 DAY>=NOW(), cotisation_expires, 0) AS cotisation_expires,
                    IF (cotisation_expires && cotisation_expires+INTERVAL 1 DAY<NOW(), cotisation_expires, 0) AS cotisation_expired
                FROM contact_contact
                WHERE contact_id={$this->contact_id}";
        $o = (object) DB()->fetchAssoc($sql);

        SESSION::get('cotisation_email') || SESSION::set('cotisation_email', $o->email);

        return parent::compose($o);
    }

    protected function composeForm($o, $f, $send)
    {
        $f->add('check', 'type', self::getCotisationTypeOptions($o));

        $f->add('textarea', 'commentaire');

        $send->attach(
            'type', 'Merci de choisir la situation qui vous correspond dans le barème de cotisation', '',
            'commentaire', '', ''
        );

        if (self::$soutien)
        {
            $item = array('item' => self::$soutien + array(
                0 => (object) array(
                    'caption' => 'Autre',
                    'onclick' => 'this.form.f_soutien.focus()',
                )
            ));

            $f->add('check', 'soutien_suggestion', $item);
            $f->add('text', 'soutien', array('valid' => 'int', 0));

            $send->attach(
                'soutien_suggestion', '', '',
                'soutien', '', ''
            );
        }

        return $o;
    }

    protected function composeAdresse($o, $f, $send, $freeze = false)
    {
        $this->adresses = new loop_edit_contact_adresseStep($f, $send, $this->contact_id);

        return $o;
    }

    protected function save($data)
    {
        self::$soutien or $data['soutien'] = 0;

        SESSION::set('cotisation_bulletin', $data);

        $data += array(
            'token' => Patchwork::strongId(8),
            'contact_id' => $this->contact_id,
            'cotisation_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            'email' => SESSION::get('cotisation_email'),
        );

        list($data['cotisation'], $data['type']) = explode('-', $data['type'], 2);

        if (!empty($data['soutien_suggestion'])) $data['soutien'] = $data['soutien_suggestion'];
        unset($data['soutien_suggestion']);

        DB()->insert('cotisation', $data);

        // TODO: envoyer un mail au secretariat si commentaire

        return 'cotiser/paiement/' . $data['token'] . '/'.$this->get->__1__;
    }


    static function getCotisationTypeOptions($o, $admin = false)
    {
        $types = array();

        $sql = "SELECT value FROM item_lists WHERE type='cotisation/type' ORDER BY sort_key";

        foreach (DB()->query($sql) as $row)
        {
            $c = explode('-', $row['value'], 2);

            $types[$row['value']] = $c[1] . ' — ' . $c[0] . ' €';
        }

        if ($admin)
        {
            $types += array(
                '0-exempté' => 'Exemption justifiée',
                '0-don' => 'Don seul',
                '0-remboursement' => 'Remboursement',
            );
        }

        return array('item' => $types);
    }
}
