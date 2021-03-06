<?php

class agent_registration_collision extends agent_pForm
{
    public $get = '__1__:c:[-_A-Za-z0-9]{8}';

    function control()
    {
        $this->get->__1__ || Patchwork::forbidden();

        $sql = "SELECT contact_id, email
                FROM contact_email
                WHERE token='registration/collision/{$this->get->__1__}'
                    AND token_expires>NOW()";
        $this->data = DB()->fetchAssoc($sql) or Patchwork::forbidden();
        $this->data = (object) $this->data;
    }

    protected function save($data)
    {
        $email = new tribes_email($this->data->contact_id);
        $email->save(
            array(
                'token' => 'user/password/' . Patchwork::strongid(8),
                'email' => $this->data->email,
            ),
            'user/password/request'
        );

        return 'registration/collision/sent';
    }
}
