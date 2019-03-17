<?php

use Patchwork as p;

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.email.dsn'] && self::sympaLogout();

        return parent::logout();
    }

    protected static function sympaLogout()
    {
        p::setcookie('sympa_session', '', 1, '/');
    }
}
