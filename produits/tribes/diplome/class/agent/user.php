<?php

class extends self
{
	protected static $selectFieldsDiplome = ', ecole, promotion';

	static function __constructStatic()
	{
		parent::__constructStatic();
		self::$selectFields .= self::$selectFieldsDiplome;
	}
}
