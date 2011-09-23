<?php

class agent_login extends self
{
    protected function login($contact)
    {
        parent::login($contact);

        if ($contact->acces)
        {
            $sql = "SELECT cotisation_expires>=NOW()
                    FROM contact_contact
                    WHERE contact_id={$contact->contact_id}";
            s::set('is_cotisant', DB()->queryOne($sql));
        }
    }
}
