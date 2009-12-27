<?php

class extends self
{
	protected function login($contact)
	{
		$CONFIG['sympa.secret'] && self::sympaLogin($contact);

		return parent::login($contact);
	}

	protected static function sympaLogin($contact)
	{
		setcookie('sympauser', $contact->email . ':' . substr( md5($contact->email . $CONFIG['sympa.secret']), -8), 0, '/wws/', $CONFIG['session.cookie_domain']);
	}
}
