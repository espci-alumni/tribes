<?php

class notification_registration_receipt extends notification
{
    protected function doSend()
    {
        parent::doSend();

        $this->mail($this->context['email']);
    }
}
