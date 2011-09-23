<?php

class agent_user_edit_contact extends agent_user_edit
{
    protected function composeForm($o, $f, $send)
    {
        return $this->composeContact($o, $f, $send);
    }

    protected function save($data)
    {
        $this->saveContact($data);

        return '';
    }
}
