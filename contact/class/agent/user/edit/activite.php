<?php

class agent_user_edit_activite extends agent_user_edit
{
    protected function composeForm($o, $f, $send)
    {
        return $this->composeActivite($o, $f, $send);
    }

    protected function save($data)
    {
        return $this->saveActivite($data);
    }
}
