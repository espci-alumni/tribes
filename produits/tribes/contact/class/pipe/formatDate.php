<?php

class
{
	protected static $format = '$3/$2/$1';

	static function __constructStatic()
	{
		self::$format = T(self::$format);
	}

	static function php($s)
	{
		return preg_replace("'(....)-(..)-(..)'u", self::$format, p::string($s));
	}

	static function js()
	{
		?>/*<script>*/

P$formatDate = function($s)
{
	return str($s).replace(/(....)-(..)-(..)/g, <?php echo jsquote(self::$format); ?>);
}

<?php	}
}



