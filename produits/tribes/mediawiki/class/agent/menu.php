<?php

class extends self
{
	static function __constructStatic()
	{
		self::$onglets['wiki'] = array(
			'titre' => 'Wiki',
			'linkto' => 'wiki/',
		);

		parent::__constructStatic();
	}
}
