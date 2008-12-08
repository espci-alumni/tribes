<?php

class extends agent_QSelect
{
	protected

	$template = 'QSelect/Search.js',
	$table = 'email';


	function compose($o)
	{
		$sql = "SELECT description AS VALUE
			FROM contact_{$this->table}
			WHERE description!=''
			GROUP BY description";

		$o->DATA = new loop_sql($sql);

		return $o;
	}
}
