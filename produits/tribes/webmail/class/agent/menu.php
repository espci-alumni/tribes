<?php

class extends self
{
	static function __constructStatic()
	{
		self::$onglets['email'] = array(
			'titre' => 'Email',
			'linkto' => $CONFIG['tribes.webmailUrl'],
		);

		parent::__constructStatic();
	}
}
