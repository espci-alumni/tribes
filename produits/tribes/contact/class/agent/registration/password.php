<?php

class extends agent_registration_collision
{
    public $get = array();

    function control() {}

    protected function composeForm($o, $f, $send)
    {
        $f->add('name', 'prenom_civil');
        $f->add('email', 'email');

        $send->attach(
            'prenom_civil', 'Veuillez renseigner votre prénom', '',
            'email', 'Veuillez renseigner votre email', ''
        );

        return $o;
    }

    protected function save($data)
    {
        $sql = $CONFIG['tribes.emailDomain'];

        if (0 === strcasecmp($sql, substr($data['email'], -strlen($sql))))
        {
            $sql = substr($data['email'], 0, -strlen($sql));
            $sql = str_replace('-', '', $sql);
            $sql = "SELECT e.contact_id, e.email
                FROM contact_email e
                    JOIN contact_alias a USING (contact_id)
                WHERE e.contact_confirmed
                    AND a.alias=" . DB()->quote($sql);
        }
        else
        {
            $sql = agent_registration::sqlSelectMatchingContact($data);
        }

        $sql = DB()->query($sql);

        if ($this->data = $sql->fetchRow())
        {
            do parent::save($data);
            while ($this->data = $sql->fetchRow());

            return 'registration/collision/sent';
        }
        else
        {
            return 'registration/password/error';
        }
    }
}
