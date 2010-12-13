<?php

class extends self
{
	static function __constructStatic()
	{
		self::$onglets['annuaire'] = array(
			'titre'  => 'Annuaire',
			'linkto' => 'annuaire/',
		);

		self::$onglets['atlas'] = array(
			'titre'  => 'Atlas',
			'linkto' => 'annuaire/atlas/',
		);

		parent::__constructStatic();
	}
}
