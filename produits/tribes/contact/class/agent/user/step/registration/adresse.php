<?php

class agent_user_step_registration_adresse extends agent_user_step_registration
{
    protected function composeForm($o, $f, $send)
    {
        return $this->composeAdresse($o, $f, $send);
    }

    protected function composeAdresse($o, $f, $send, $freeze = false)
    {
        $o = parent::composeAdresse($o, $f, $send, true);

        $this->adresses = new loop_edit_contact_adresseStep($f, $send, $this->contact_id);

        return $o;
    }

    protected function save($data)
    {
        $this->saveAdresse($data);

        return parent::save($data);
    }
}
