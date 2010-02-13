<?php

class extends self
{
	function compose($o)
	{
		$o = parent::compose($o);

		$o->js_navigation_url = dirname($CONFIG['tribes.annuaire.syncUrl']) . '/js/navigation';

		return $o;
	}
}
