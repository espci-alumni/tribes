<?php

class notification_user_email extends notification
{
    protected function doSend()
    {
        parent::doSend();

        $c =& $this->context;

        if (!empty($c['token']) && !empty($c['email']))
        {
            $this->mail($c['email']);
        }
    }
}
