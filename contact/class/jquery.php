<?php

class extends self
{
	static function __constructStatic()
	{
		self::$uiLoad .= ' ui.datepicker';

		parent::__constructStatic();
	}
}
