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
		$s = p::string($s);

		return (int) $s ? preg_replace("'(....)-(..)-(..)'u", self::$format, $s) : '';
	}

	static function js()
	{
		?>/*<script>*/

function($s)
{
	$s = str($s);
	return parseInt($s) ? $s.replace(/(....)-(..)-(..)/g, <?php echo jsquote(self::$format); ?>) : '';
}

<?php	}
}
