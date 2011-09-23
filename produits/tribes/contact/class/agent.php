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
                SESSION::flash('referer', substr(patchwork::__URI__(), strlen(patchwork::__BASE__())));
                patchwork::redirect('login');
            }

            tribes::connectedIsAuth($this->requiredAuth) || patchwork::forbidden();

            $this->connected_is_admin = tribes::connectedIsAuth('admin');
        }
    }
}
