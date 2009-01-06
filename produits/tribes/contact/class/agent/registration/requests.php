<?php

class extends agent
{
	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = "SELECT sexe, nom_civil, prenom_civil, date_naissance, token
				FROM contact_contact
				WHERE statut_inscription='demande'
				ORDER BY contact_confirmed";

		$o->contacts = new loop_sql($sql);

		return $o;
	}
}
