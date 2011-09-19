<?php

class extends agent_login
{
    protected function composeForm($o, $f, $send)
    {
        $f->add('check', 'sexe', array('item' => array(
            'F' => 'Mme, Mlle',
            'M' => 'M.'
        )));

        $f->add('name', 'nom_civil');
        $f->add('name', 'prenom_civil');
        $f->add('email', 'email');
        $f->add('password', 'password');

        $send->attach(
            'sexe', "Veuillez renseigner le champs Mme Mlle M.", '',
            'nom_civil', "Veuillez renseigner votre nom", '',
            'prenom_civil', "Veuillez renseigner votre prénom", '',
            'email', "Veuillez renseigner votre email", '',
            'password', "Veuillez renseigner votre nouveau mot de passe", ''
        );

        return $o;
    }


    protected function save($data)
    {
        $db = DB();

        $sql = self::sqlSelectMatchingContact($data);

        if ($contact = $db->queryOne($sql))
        {
            $this->data = (object) $data;
            $this->data->contact_id = $contact;

            $token = p::strongid(8);

            $sql = "UPDATE contact_email
                    SET token='registration/collision/{$token}',
                        token_expires=NOW()+INTERVAL 60 MINUTE,
                        is_obsolete=IF(is_obsolete,-1,0)
                    WHERE contact_id={$contact}
                        AND email=" . DB()->quote($data['email']) . "
                        AND contact_confirmed";
            $db->exec($sql);

            return "registration/collision/{$token}";
        }

        $next_step = new tribes_step_registration();
        $next_step = $next_step->getNextStep();

        $data += array(
            'nom_etudiant' => $data['nom_civil'],
            'nom_usuel' => $data['nom_civil'],
            'prenom_usuel' => $data['prenom_civil'],
            'photo_token' => p::strongid(8),
            'cv_token' => p::strongid(8),
            'token' => 'confirm/registration/' . p::strongid(8),
            'origine' => 'registration',
            'etape_suivante' => $next_step,
            'contact_confirmed' => true,
        );

        $contact = new tribes_contact(0, false);
        $contact->save($data, 'registration/receipt');

        $this->data = (object) $data;
        $this->data->contact_id = $contact->contact_id;

        $data['is_active'] = 1;
        $contact = new tribes_email($contact->contact_id, false);
        $contact->save($data, false);

        $data['login'] = $data['email'];
        $data = parent::save($data);

        return false !== $data ? "user/step/{$next_step}" : false;
    }

    static function sqlSelectMatchingContact($data)
    {
        $pattern = 'REPLACE(%s,"%s","")';
        $sql = sprintf($pattern, '%s', "'");
        $sql = sprintf($pattern, $sql, " ");
        $sql = sprintf($pattern, $sql, "-");

        $pattern = '%s LIKE CONCAT(%s,"%%%%")';
        $pattern = sprintf($pattern, '%1$s', '%2$s') . ' OR ' . sprintf($pattern, '%2$s', '%1$s');
        $sql = sprintf(
                $pattern,
                sprintf($sql, 'prenom_civil'),
                sprintf($sql, DB()->quote($data['prenom_civil']))
            ) . ' OR ' . sprintf(
                $pattern,
                sprintf($sql, 'prenom_usuel'),
                sprintf($sql, DB()->quote($data['prenom_civil']))
            );

        $sql = "SELECT c.contact_id, e.email
                FROM contact_contact c
                    JOIN contact_email e USING (contact_id)
                WHERE e.email=" . DB()->quote($data['email']) . "
                    AND ({$sql})
                    AND c.password!=''
                    AND c.acces!=''
                    AND c.is_obsolete=0";

        return $sql;
    }
}
