<?php

class extends loop_edit_user_adresse
{
	function __construct($f, $contact_id)
	{
		$sql = "SELECT adresse_id,
					description  AS c_description,
					adresse      AS c_adresse,
					ville_avant  AS c_ville_avant,
					ville        AS c_ville,
					ville_apres  AS c_ville_apres,
					pays         AS c_pays,
					email_list   AS c_email_list,
					tel_portable AS c_tel_portable,
					tel_fixe     AS c_tel_fixe,
					tel_fax      AS c_tel_fax,
					admin_confirmed,
					contact_data
				FROM contact_adresse
				WHERE contact_id={$contact_id}
					AND admin_confirmed<contact_modified";

		$loop = new loop_sql($sql, array($this, 'filterAdresse'));

		loop_edit::__construct($f, $loop);
	}

	function filterAdresse($o)
	{
		$o = (object) ((array) $o + unserialize($o->contact_data));

		!(int) $o->admin_confirmed && $o->new_adresse = 1;

		return $o;
	}
}
