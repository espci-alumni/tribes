<?php

class extends self
{
	protected function logout()
	{
		$CONFIG['sympa.secret'] && self::sympaLogout();

		return parent::logout();
	}

	protected static function sympaLogout()
	{
		setcookie('sympauser', '', 1, '/wws/', $CONFIG['session.cookie_domain']);
	}
}
