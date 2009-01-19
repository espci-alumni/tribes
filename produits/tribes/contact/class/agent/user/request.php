<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1');

	protected
	
	$requiredAuth = 'admin',
	$confirmed = true,
	$form,
	$send,
	$adresses;

	function control()
	{
		$this->contact_id = $this->get->__1__;
		$this->get->adresse = false;

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
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composePhoto($o, $f, $send);

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
		$sql = "SELECT adresse_id,
					description  AS c_description,
					adresse      AS c_adresse,
					ville_avant  AS c_ville_avant,
					city_id      AS c_city_id,
					ville        AS c_ville,
					pays         AS c_pays,
					ville_apres  AS c_ville_apres,
					email_list   AS c_email_list,
					tel_portable AS c_tel_portable,
					tel_fixe     AS c_tel_fixe,
					tel_fax      AS c_tel_fax,
					admin_confirmed,
					contact_data
				FROM contact_adresse
				WHERE contact_id={$this->contact_id}
					AND admin_confirmed<contact_modified";

		$o->adresses = $this->adresses = new loop_sql($sql, array($this, 'filterAdresse'));

		return $o;
	}

	protected function save($data)
	{
		$this->saveContact($data);
		
		if ($this->photoField)
		{
			if ($data['delete'])
			{
				@unlink(patchworkPath('data/photo/') . $this->data->photo_token . '.contact.jpg');
			}

			$this->savePhoto();
		}

		$this->saveAdresse($data);

		return 'user/requests';
	}

	protected function saveAdresse($data)
	{
		while ($data = $this->adresses->loop())
		{
			$this->data->adresse_id = $data->adresse_id;

			parent::saveAdresse(array(
				'description'  => $data->f_description->getDbValue(),
				'adresse'      => $data->f_adresse->getDbValue(),
				'ville_avant'  => $data->f_ville_avant->getDbValue(),
				'ville'        => $data->f_ville->getDbValue(),
				'ville_apres'  => $data->f_ville_apres->getDbValue(),
				'email_list'   => $data->f_email_list->getDbValue(),
				'tel_portable' => $data->f_tel_portable->getDbValue(),
				'tel_fixe'     => $data->f_tel_fixe->getDbValue(),
				'tel_fax'      => $data->f_tel_fax->getDbValue(),
			));
		}
	}

	function filterAdresse($o)
	{
		$o = (object) ((array) $o + unserialize($o->contact_data));

		empty($o->city_id) || $o->ville = $o->city_id . ':' . $o->ville . ', ' . $o->pays;

		$this->form->pushContext($o, 'adresse_' . $o->adresse_id);

		$this->form->setDefaults($o);
		$o = $this->composeFormAdresse($o, $this->form, $this->send);

		!(int) $o->admin_confirmed && $o->new_adresse = 1;

		$this->form->pullContext();

		return $o;
	}
}
