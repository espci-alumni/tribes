<?php

class notification_user_cotisation extends self
{
    protected function doSend()
    {
        if (empty($this->context['email']))
        {
            $sql = "SELECT login
                    FROM contact_contact
                    WHERE contact_id={$this->contact_id}
                        AND admin_confirmed
                        AND contact_confirmed
                        AND login!=''";
            if ($this->context['email'] = DB()->fetchColumn($sql))
            {
                $this->context['email'] .= $CONFIG['tribes.emailDomain'];
            }
        }

        parent::doSend();
    }
}
