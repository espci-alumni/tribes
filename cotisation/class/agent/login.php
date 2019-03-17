<?php

class agent_login extends self
{
    protected function login($contact)
    {
        parent::login($contact);

        if ($contact->acces)
        {
            $sql = "SELECT cotisation_expires>=NOW() AS is_cotisant, LEAST(CAST(cotisation_expires AS CHAR),CONCAT(YEAR(NOW()),'-12-31')) AS cotisation_expires
                    FROM contact_contact
                    WHERE contact_id={$contact->contact_id}";
            SESSION::set(DB()->fetchAssoc($sql));
        }
    }
}
