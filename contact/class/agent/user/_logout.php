<?php

class agent_user___x5Flogout extends agent
{
    protected

    $maxage = -1,
    $requiredAuth = false;


    function compose($o)
    {
        $o->form_logout = new pForm(false);
        $o->f_logout = $o->form_logout->add('submit', 'logout');

        if ($o->f_logout->isOn() && tribes::getConnectedId())
        {
            $this->logout();
        }

        return $o;
    }

    protected function logout()
    {
        SESSION::destroy();
        Patchwork::redirect('index');
    }
}
