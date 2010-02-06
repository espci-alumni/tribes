<?php

class extends agent
{
	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = "SELECT contact_data, token, contact_modified
				FROM contact_contact
				WHERE statut_inscription='demande'
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
