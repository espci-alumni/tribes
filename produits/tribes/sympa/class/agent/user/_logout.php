<?php

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.email.dsn'] && self::sympaLogout();

        return parent::logout();
    }

    protected static function sympaLogout()
    {
        setcookie('sympa_session', '', 1, '/wws/', $CONFIG['session.cookie_domain']);
    }
}
