<?php

class extends self
{
	protected function logout()
	{
		$CONFIG['tribes.phpbbDb'] && self::phpbbLogout();

		return parent::logout();
	}

	protected static function phpbbLogout()
	{
		setcookie($CONFIG['tribes.phpbbDb'] . '_u'  , '', 1, '/forum/', $CONFIG['session.cookie_domain']);
		setcookie($CONFIG['tribes.phpbbDb'] . '_sid', '', 1, '/forum/', $CONFIG['session.cookie_domain']);
	}
}
