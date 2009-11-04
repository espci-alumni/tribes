<?php

class extends self
{
	const ACCUEIL_CONNECTED = 'wiki/Accueil';

	static function __constructStatic()
	{
		self::$onglets['wiki'] = array(
			'titre' => 'Wiki',
			'linkto' => 'wiki/',
		);

		parent::__constructStatic();
	}
}
