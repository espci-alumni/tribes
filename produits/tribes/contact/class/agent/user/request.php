<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1');

	protected

	$requiredAuth = 'admin',
	$confirmed = true,
	$form,
	$send,
	$adresses,
	$activites;


	function control()
	{
		$this->contact_id = $this->get->__1__;

		parent::control();
	}

	function compose($o)
	{
		$o = $this->data;

		$sql = "SELECT sexe        AS c_sexe,
					prenom_usuel   AS c_prenom_usuel,
					nom_usuel      AS c_nom_usuel,
					prenom_civil   AS c_prenom_civil,
					nom_civil      AS c_nom_civil,
					nom_etudiant   AS c_nom_etudiant,
					date_naissance AS c_date_naissance,
					login          AS c_login
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";

		$sql = (array) DB()->queryRow($sql);

		$o = (object) ((array) $o + $sql);

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$this->form = $f;
		$this->send = $send;

		$o = $this->composeContact($o, $f, $send);
		$o = $this->composePhoto($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);

		return $o;
	}

	protected function composePhoto($o, $f, $send)
	{
		$file = patchworkPath('data/photo/') . $this->data->photo_token . '.contact.jpg';

		if ($o->newPhoto = file_exists($file))
		{
			$f->add('check', 'delete', array(
				'firstItem' => '---',
				'item' => array(0 => 'Publier', '1' => 'Rejeter')
			));

			$this->photoField = $f->add('file', 'photo', array('valid' => 'image', null, array('jpg','gif','png')));
		
			$send->attach(
				'delete', 'Veuillez choisir une action sur la photo', '',
				'photo', '', "Format d'image non valide"
			);
		}

		return $o;
	}

	protected function composeAdresse($o, $f, $send)
	{
		$this->adresses = $o->adresses = new loop_edit_contact_adresseDiff($f, $this->contact_id, $send);

		return $o;
	}

	protected function composeActivite($o, $f, $send)
	{
		$this->activites = $o->activites = new loop_edit_contact_activiteDiff($f, $this->contact_id, $send);

		return $o;
	}

	protected function save($data)
	{
		if (isset($this->photoField) && $data['delete'])
		{
			@unlink(patchworkPath('data/photo/') . $this->data->photo_token . '.contact.jpg');
		}

		$this->saveContact($data);
		$this->saveAdresse($data);
		$this->saveActivite($data);

		return 'user/requests';
	}
}
