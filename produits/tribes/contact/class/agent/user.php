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
					login,
					sexe,
					prenom_usuel,
					nom_usuel,
					nom_etudiant,
					IF(date_naissance,date_naissance,'') AS date_naissance,
					statut_inscription,
					photo_token,
					cv_token
				FROM contact_contact
				WHERE contact_id={$this->get->__1__}";
		$this->contact = DB()->queryRow($sql);

		$this->contact || p::forbidden();
	}

	function compose($o)
	{
		$o = $this->contact;

		$o->hasPhoto = file_exists(patchworkPath('data/photo/') . $o->photo_token . '.jpg');
		$o->hasCv    = file_exists(patchworkPath('data/cv/')    . $o->cv_token    . '.pdf');

		$o->adresses  = new loop_user_adresse($this->contact->contact_id);
		$o->activites = new loop_user_activite($this->contact->contact_id);

		return $o;
	}
}