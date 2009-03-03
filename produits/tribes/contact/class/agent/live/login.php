<?php

class extends agent
{
	public $get = 'q:c';

	function control() {}

	function compose($o)
	{
		$sql = $this->get->q;
		'*' == $sql && $sql = '';

		$sql = DB()->quote($sql . '%');

		$sql = "SELECT login
				FROM contact_contact
				WHERE login LIKE {$sql} AND login!=''
				ORDER BY login";
		$o->logins = new loop_sql($sql, '', 0, 15);

		return $o;
	}
}
