<?php

class notification_registration_accepted extends notification
{
    protected function doSend()
    {
        parent::doSend();

        $sql = "SELECT email
                FROM contact_email
                WHERE contact_id={$this->contact_id}
                    AND is_active
                    AND contact_confirmed";

        $this->mail(DB()->query($sql)->fetchAll(PDO::FETCH_COLUMN));
    }
}
