<?php

class notification_user_cotisation extends notification
{
    protected function doSend()
    {
        parent::doSend();

        if (!((float) $this->context['paiement_euro']) && ((float) $this->context['soutien']))
        {
        }
        else if (empty($this->context['email']))
        {
            $has_active = false;

            $sql = "SELECT email, is_active
                    FROM contact_email
                    WHERE contact_id={$this->contact_id}
                        AND is_obsolete<=0
                    ORDER BY is_active DESC";
            $result = DB()->query($sql);

            while ($row = $result->fetchRow())
            {
                if ($has_active && !$row->is_active) break;

                $this->mail($row->email);

                $row->is_active && $has_active = true;
            }
        }
        else
        {
            $this->mail($this->context['email']);
        }
    }
}
