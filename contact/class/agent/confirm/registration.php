<?php

class agent_confirm_registration extends agent_confirm_email
{
    function control()
    {
        $sql = implode(',', tribes::getDataFields(array('contact', 'email')));
        $sql = "SELECT contact_id,{$sql}
                FROM contact_contact c
                    JOIN contact_email e USING (contact_id)
                WHERE e.token='confirm/registration/{$this->get->__1__}'
                    AND e.token_expires>NOW()";
        $data = DB()->fetchAssoc($sql) or Patchwork::redirect('error/token');

        tribes_email::confirm("confirm/registration/{$this->get->__1__}");

        notification::send('registration/request', $data);
    }
}
