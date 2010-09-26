<?php

class extends self
{
	static function __constructStatic()
	{
		self::$selectFields .= ', ecole, promotion';
	}
}
