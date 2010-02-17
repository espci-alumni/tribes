<?php

class extends loop_sql
{
	function __construct($contact_id)
	{
		$sql = "SELECT adresse_id,
					description,
					adresse,
					ville_avant,
					ville,
					ville_apres,
					pays,
					tel_portable,
					tel_fixe,
					tel_fax
				FROM contact_adresse
				WHERE contact_id={$contact_id}
					AND admin_confirmed
					AND is_shared
					AND is_obsolete<=0
					AND description!=''
				ORDER BY sort_key";

		parent::__construct($sql);
	}
}
