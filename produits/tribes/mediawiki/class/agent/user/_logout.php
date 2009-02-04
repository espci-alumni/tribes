<?php

class extends self
{
	protected function logout()
	{
		$CONFIG['tribes.mediaWikiDb'] && self::mediaWikiLogout();

		return parent::logout();
	}

	protected static function mediaWikiLogout()
	{
		setcookie($CONFIG['tribes.mediaWikiDb'] . 'UserID'  , '', 1, '/wiki/', $CONFIG['session.cookie_domain']);
		setcookie($CONFIG['tribes.mediaWikiDb'] . 'UserName', '', 1, '/wiki/', $CONFIG['session.cookie_domain']);
		setcookie($CONFIG['tribes.mediaWikiDb'] . 'Token'   , '', 1, '/wiki/', $CONFIG['session.cookie_domain']);
		setcookie($CONFIG['tribes.mediaWikiDb'] . '_session', '', 1, '/'     , $CONFIG['session.cookie_domain']);
		setcookie($CONFIG['tribes.mediaWikiDb'] . 'LoggedOut', $_SERVER['REQUEST_TIME'], 0, '/', $CONFIG['session.cookie_domain']);
	}
}	
}
