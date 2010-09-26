<?php

class extends self
{
	protected function logout()
	{
		$CONFIG['tribes.webmailUrl'] && self::webmailLogout();

		return parent::logout();
	}

	protected static function webmailLogout()
	{
		setcookie('tribes_webmail', '', 1, $CONFIG['tribes.webmailPath'], $CONFIG['session.cookie_domain']);
	}
}
