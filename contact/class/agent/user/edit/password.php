<?php

class agent_user_edit_password extends agent_user_edit
{
    protected function composeForm($o, $f, $send)
    {
        $o = $this->composeNewPassword($o, $f, $send);
        return $this->composePassword($o, $f, $send);
    }

    protected function save($data)
    {
        $this->contact->save($data);

        return '';
    }
}
