<?php

class extends agent
{
	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = "SELECT 1
				FROM contact_email
				WHERE is_active
					AND contact_id=c.contact_id
					AND contact_confirmed
					AND admin_confirmed
					AND is_obsolete<=0
				LIMIT 1";

		$sql = "SELECT contact_data, contact_id, contact_modified, etape_suivante,
					($sql) AS has_active_email
				FROM contact_contact c
				WHERE password!=''
					AND acces=''
					AND is_obsolete=0
				ORDER BY contact_modified";

		$o->contacts = new loop_sql($sql, array($this, 'filterContact'));

		return $o;
	}

	function filterContact($o)
	{
		$o->contact_data && $o = (array) $o + unserialize($o->contact_data);

		unset($o->contact_data);

		return $o;
	}
}
