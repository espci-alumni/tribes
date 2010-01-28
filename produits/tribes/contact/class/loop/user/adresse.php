<?php

class extends loop_sql
{
	protected

	$table = 'adresse',
	$select = '
		description,
		adresse,
		ville_avant,
		ville,
		ville_apres,
		pays,
		tel_portable,
		tel_fixe,
		tel_fax';


	function __construct($contact_id)
	{
		$sql = "SELECT {$this->table}_id,
					{$this->select}
				FROM contact_{$this->table}
				WHERE contact_id={$contact_id}
					AND admin_confirmed
					AND is_shared
					AND is_obsolete<=0
				ORDER BY sort_key";

		parent::__construct($sql);
	}
}
