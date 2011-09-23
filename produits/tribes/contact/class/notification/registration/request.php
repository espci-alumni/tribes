<?php

class notification_registration_request extends notification
{
    protected function doSend()
    {
        parent::doSend();

        $sql = "SELECT e.email
                FROM contact_email e
                    JOIN contact_contact c USING (contact_id)
                WHERE c.acces='admin'
                    AND e.is_active
                    AND e.contact_confirmed
                    AND e.admin_confirmed";

        $this->mail(DB()->queryCol($sql));
    }
}
