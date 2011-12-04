<?php

class agent extends self
{
    protected

    $requiredAuth = 'membre',
    $connected_id = 0,
    $connected_is_admin = false;

    function control()
    {
        if ($this->requiredAuth)
        {
            $this->connected_id = tribes::getConnectedId();

            if (!$this->connected_id)
            {
                SESSION::flash('referer', substr(Patchwork::__URI__(), strlen(Patchwork::__BASE__())));
                Patchwork::redirect('login');
            }

            tribes::connectedIsAuth($this->requiredAuth) || Patchwork::forbidden();

            $this->connected_is_admin = tribes::connectedIsAuth('admin');
        }
    }
}
