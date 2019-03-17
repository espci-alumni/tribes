<?php

use Patchwork as p;

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.phpbbDb'] && self::phpbbLogout();

        return parent::logout();
    }

    protected static function phpbbLogout()
    {
        p::setcookie($CONFIG['tribes.phpbbDb'] . '_u', '', 1, $CONFIG['tribes.phpbbPath']);
        p::setcookie($CONFIG['tribes.phpbbDb'] . '_sid', '', 1, $CONFIG['tribes.phpbbPath']);
    }
}
