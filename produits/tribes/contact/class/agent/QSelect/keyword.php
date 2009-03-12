<?php

class extends agent_QSelect
{
	protected 
		
	$template = 'QSelect/Suggest.js',
	$requiredAuth = false;

	function compose($o)
	{
		$sql = "SELECT suggestion AS VALUE
				FROM item_suggestions
				WHERE type='keyword'
				ORDER BY suggestion";

		$o->DATA = new loop_sql($sql);
		$o->separator = ', ';
		$o->separatorRx = '\s*[,;\\/]\s*';

		return $o;
	}
}
