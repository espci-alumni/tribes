<?php

class extends agent_QSelect
{
	public $get = '__1__:c:fonction|secteur';


	protected

	$template = 'QSelect/Search.js',
	$requiredAuth = false;


	function compose($o)
	{
		$sql = $this->get->__1__;

		$sql = "SELECT {$sql} AS VALUE
				FROM contact_activite
				WHERE {$sql}!='' AND is_obsolete<=0
				GROUP BY {$sql}";

		$o->DATA = new loop_sql($sql);

		return $o;
	}
}
