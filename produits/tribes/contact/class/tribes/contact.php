<?php

class extends tribes_common
{
	protected

	$table = 'contact',

	$dataFields = array(
		'sexe',
		'nom_civil',
		'prenom_civil',
		'nom_usuel',
		'prenom_usuel',
		'nom_etudiant',
		'date_naissance',
		'date_deces',
		'conjoint_contact_id',
	);

	function __construct($contact_id, $confirmed = 0)
	{
		$this->metaFields += array(
			'token'              => 'stringNull',
			'token_expires'      => 'sql',
			'statut_inscription' => 'string',
			'login'              => 'string',
			'password'           => 'string',
		);

		parent::__construct($contact_id, $confirmed);
	}

	function save($data, $message = null, $id = 0)
	{
		return parent::save($data, $message, $this->contact_id);
	}
}
