<?php

class extends agent
{
	public $get = '__1__:i:1';

	protected $contact;

	function control()
	{
		$this->get->__1__ || p::forbidden();

		parent::control();

		$sql = "SELECT contact_id,
					sexe,
					prenom_usuel,
					nom_usuel,
					prenom_civil,
					nom_civil,
					nom_etudiant,
					date_naissance,
					statut_inscription,
					photo_token
				FROM contact_contact
				WHERE contact_id={$this->get->__1__}";
		$this->contact = DB()->queryRow($sql);

		$this->contact || p::forbidden();
	}

	function compose($o)
	{
		$o = $this->contact;

		$o->adresses  = new loop_user_adresse($this->contact->contact_id);
		$o->activites = new loop_user_activite($this->contact->contact_id);

		return $o;
	}
}
