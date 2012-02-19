<?php

class agent_login extends self
{
    protected function login($contact)
    {
        parent::login($contact);

        if ($contact->acces)
        {
            $sql = "SELECT cotisation_expires>=NOW() AS is_cotisant, cotisation_expires
                    FROM contact_contact
                    WHERE contact_id={$contact->contact_id}";
            SESSION::set(DB()->fetchAssoc($sql));
        }
    }
}
