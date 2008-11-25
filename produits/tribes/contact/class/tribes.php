<?php

class
{
	const PENDING_PERIOD = '4 HOUR';

	static function getAdminEmails()
	{
		return array('iekhad@hotmail.fr');
	}

	static function filterLogin($a)
	{
		$a = p::toASCII($a);
		$a = strtolower($a);
		$a = preg_replace("/[^a-z]+/", '', $a);

		return $a;
	}
}
