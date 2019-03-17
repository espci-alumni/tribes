<?php

use Patchwork as p;

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.webmailUrl'] && self::webmailLogout();

        return parent::logout();
    }

    protected static function webmailLogout()
    {
        p::setcookie('tribes_webmail', '', 1, $CONFIG['tribes.webmailPath']);
    }
}
