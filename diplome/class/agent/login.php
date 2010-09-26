<?php

class extends self
{
	static function __constructStatic()
	{
		parent::__constructStatic();

		self::$sessionFields .= ', promotion';
	}
}
