<?php

class extends agent
{
	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = "SELECT
					sexe,
					nom_civil,
					prenom_civil,
					date_naissance,
					contact_id
				FROM contact_contact
				WHERE statut_inscription='accepted'
					AND admin_confirmed<contact_modified
				ORDER BY contact_modified";

		$o->contacts = new loop_sql($sql);

		return $o;
	}
}
