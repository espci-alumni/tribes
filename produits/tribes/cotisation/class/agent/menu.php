<?php

class extends self
{
	function compose($o)
	{
		$o = parent::compose($o);

		$o->is_cotisant = s::get('is_cotisant');

		return $o;
	}
}
