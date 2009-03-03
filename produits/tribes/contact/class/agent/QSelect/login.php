<?php

class extends agent_QSelect
{
	protected

	$maxage = -1,
	$template = 'QSelect/liveAgent.js';


	function control() {}

	function compose($o)
	{
		$o->src = 'live/login';
		$o->loop = 'logins';
		$o->key = 'login';

		return $o;
	}
}
