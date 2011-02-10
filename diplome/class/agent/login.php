<?php

class extends self
{
	protected static $sessionFieldsDiplome = ', promotion';

	static function __constructStatic()
	{
		parent::__constructStatic();
		self::$sessionFields .= self::$sessionFieldsDiplome;
	}
}
