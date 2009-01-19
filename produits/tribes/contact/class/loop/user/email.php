<?php

class extends loop_sql
{
	protected $table = 'email';

	function __construct($contact_id)
	{
		$sql = "SELECT {$this->table}_id AS id, is_active, is_obsolete, admin_confirmed, contact_confirmed, contact_data
				FROM contact_{$this->table}
				WHERE contact_id={$contact_id} AND is_obsolete<=0 AND contact_data!=''
				ORDER BY sort_key";

		parent::__construct($sql, array($this, 'filterRow'));
	}

	function filterRow($o)
	{
		$o = (object) ((array) $o + (array) unserialize($o->contact_data));

		unset($o->contact_data);

		(int) $o->admin_confirmed   || $o->admin_confirmed = 0;
		(int) $o->contact_confirmed || $o->contact_confirmed = 0;

		return $o;
	}
}
