<?php

class extends self
{
	static function __constructStatic()
	{
		parent::__constructStatic();

		self::$types['cotisation/type'] = false;
	}
}
