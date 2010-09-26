<?php

class extends self
{
	static function __constructStatic()
	{
		self::$onglets['annuaire'] = array(
			'titre'  => 'Annuaire',
			'linkto' => 'annuaire/',
		);

		parent::__constructStatic();
	}
}
