<?php

class agent_user_edit_adresse extends agent_user_edit
{
    public $get = array('__1__:c:activite');

    protected function composeForm($o, $f, $send)
    {
        return $this->composeAdresse($o, $f, $send, (bool) $this->get->__1__);
    }

    protected function save($data)
    {
        $this->saveAdresse($data);

        return $this->get->__1__ ? 'user/edit/activite' : '';
    }
}
