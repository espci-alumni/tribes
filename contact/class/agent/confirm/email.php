<?php

class agent_confirm_email extends agent
{
    public $get = '__1__:c:[-_A-Za-z0-9]{8}';

    function control()
    {
        $this->get->__1__ || patchwork::forbidden();

        tribes_email::confirm("confirm/email/{$this->get->__1__}") || patchwork::redirect('error/token');
    }
}
