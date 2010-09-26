<?php

class extends agent_QSelect
{
	protected

	$template = 'QSelect/Suggest.js',
	$requiredAuth = false;


	function compose($o)
	{
		$sql = "SELECT organisation AS VALUE
				FROM contact_organisation
				WHERE is_obsolete<=0
				ORDER BY organisation";

		$o->DATA = new loop_sql($sql);
		$o->separator = ' / ';
		$o->separatorRx = '\s*[;\\/]\s*';

		return $o;
	}
}
