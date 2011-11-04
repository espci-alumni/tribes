<?php

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.phpbbDb'] && self::phpbbLogout();

        return parent::logout();
    }

    protected static function phpbbLogout()
    {
        setcookie($CONFIG['tribes.phpbbDb'] . '_u', '', 1, $CONFIG['tribes.phpbbPath'], $CONFIG['session.cookie_domain']);
        setcookie($CONFIG['tribes.phpbbDb'] . '_sid', '', 1, $CONFIG['tribes.phpbbPath'], $CONFIG['session.cookie_domain']);
    }
}
