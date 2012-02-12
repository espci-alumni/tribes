<?php

class agent_user_step_registration_activite extends agent_user_step_registration
{
    protected function composeForm($o, $f, $send)
    {
        return $this->composeActivite($o, $f, $send);
    }

    protected function composeActivite($o, $f, $send, $freeze = false)
    {
        $o = parent::composeActivite($o, $f, $send, true);

        $this->activites = new loop_edit_contact_activiteStep($f, $send, $this->contact_id);

        return $o;
    }

    protected function save($data)
    {
        $this->saveActivite($data);

        return parent::save($data);
    }
}
