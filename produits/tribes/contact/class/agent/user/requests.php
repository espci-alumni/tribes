<?php

class extends agent
{
	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = "SELECT sexe, nom_civil, prenom_civil, date_naissance, contact_id
				FROM contact_contact
				WHERE statut_inscription='accepted'
					AND admin_confirmed<contact_confirmed
				ORDER BY contact_confirmed";

		$o->contacts = new loop_sql($sql);

		return $o;
	}
}
