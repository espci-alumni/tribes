<?php

use Patchwork as p;

class agent_user___x5Flogout extends self
{
    protected function logout()
    {
        $CONFIG['tribes.mediaWikiDb'] && self::mediaWikiLogout();

        return parent::logout();
    }

    protected static function mediaWikiLogout()
    {
        p::setcookie($CONFIG['tribes.mediaWikiDb'] . 'UserID', '', 1, $CONFIG['tribes.mediaWikiPath']);
        p::setcookie($CONFIG['tribes.mediaWikiDb'] . 'UserName', '', 1, $CONFIG['tribes.mediaWikiPath']);
        p::setcookie($CONFIG['tribes.mediaWikiDb'] . 'Token', '', 1, $CONFIG['tribes.mediaWikiPath']);
        p::setcookie($CONFIG['tribes.mediaWikiDb'] . '_session', '', 1, '/');
        p::setcookie($CONFIG['tribes.mediaWikiDb'] . 'LoggedOut', $_SERVER['REQUEST_TIME'], 0, '/');
    }
}
