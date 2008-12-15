<?php

class extends agent_registration
{
	public $get = array('adresse:i:1', 'contact:i:1' => 1);

	protected

	$maxage = 0,
	$connected_id = true,
	$mandatoryEmail   = false,
	$mandatoryAdresse = false,

	$contact_id,
	$confirmed = false,
	$contact = false,
	$email = false,
	$adresse = false;


	function control()
	{
		parent::control();

		$this->contact_id = $this->get->contact;

		tribes::requireAuth('user/edit', $this->contact_id);

		$this->contact = new tribes_contact($this->contact_id, $this->confirmed);

		$this->data = (array) $this->data;
		$this->data += $this->contact->fetchRow('contact_id, contact_data');

		$this->email = new tribes_email($this->contact_id, $this->confirmed);

		$this->adresse = new tribes_adresse($this->contact_id, $this->confirmed);

		if ($this->get->adresse)
		{
			$data = $this->adresse->fetchRow('adresse_id, contact_data', $this->get->adresse);
			isset($data['pays']) && $data['ville'] .= ', ' . $data['pays'];
			unset($data['description']);
			$this->data += $data;
		}

		$sql = "SELECT 1 FROM contact_email
				WHERE contact_id={$this->contact_id}
					AND is_obsolete<=0 AND contact_data!=''
				LIMIT 1";
		$this->mandatoryEmail = DB()->queryOne($sql) ? false : true;

		$sql = "SELECT 1 FROM contact_adresse
				WHERE contact_id={$this->contact_id}
					AND is_obsolete<=0 AND contact_data!=''
				LIMIT 1";
		$this->mandatoryAdresse = DB()->queryOne($sql) ? false : true;

		$this->data = (object) $this->data;
		$this->data->contact_id =& $this->contact_id;
	}

	function compose($o)
	{
		$o = parent::compose($o);

		$o->emails   = new loop_user_edit_email($this->contact_id, $o->form);
		$o->adresses = new loop_user_edit_adresse($this->contact_id, $o->form);

		return $o;
	}

	protected function composeForm($f, $send)
	{
		parent::composeForm($f, $send);

		$this->composeFormAdresse($f, $send);
	}

	protected function formIsOk($f)
	{
		if (!$f->getElement('adresse')->getStatus())
		{
			$adresse = array(
				'description',
				'ville_avant',
				'ville',
				'ville_apres',
				'email_list',
				'tel_portable',
				'tel_fixe',
				'tel_fax',
			);

			foreach ($adresse as $adresse) if ($f->getElement($adresse)->getStatus())
			{
				!$f->getElement('adresse')->setError('Veuillez saisir une adresse');
				return false;
			}
		}

		if ($f->getElement('adresse')->getStatus() && !$f->getElement('ville')->getStatus())
		{
			!$f->getElement('ville')->setError('Veuillez renseigner une ville');
			return false;
		}

		return parent::formIsOk($f);
	}

	protected function composeFormContact($f, $send)
	{
		parent::composeFormContact($f, $send);

		$f->add('text', 'nom_etudiant', self::$altern_case_rx);
		$f->add('text', 'nom_usuel',    self::$altern_case_rx);
		$f->add('text', 'prenom_usuel', self::$altern_case_rx);

		$send->attach(
			'nom_etudiant', '', self::$altern_case_msg,
			'nom_usuel',    '', self::$altern_case_msg,
			'prenom_usuel', '', self::$altern_case_msg
		);
	}

	protected function composeFormEmail($f, $send)
	{
		$f->add('textarea', 'email', array(
			'valid' => 'text', '.*' . FILTER::EMAIL_RX . '.*',
		));

		$send->attach('email', '', '');

		$action = array(0 => 'Confirmer', 1 => 'Supprimer');
		$this->confirmed && $action[-1] = 'À vérifier';

		$f->add('select', 'email_is_obsolete', array(
			'firstItem' => '---',
			'item' => $action
		));

		$confirm = $f->add('submit', 'email_confirm');
		$confirm->attach('email_is_obsolete', 'Quelle action effectuer sur les emails sélectionnés ?', '');

		if ($confirm->isOn())
		{
			$this->saveEmails($confirm->getData());
			p::redirect();
		}
	}

	protected function composeFormAdresse($f, $send)
	{
		$f->add('textarea', 'adresse');

		$f->add('QSelect', 'description', array(
			'src' => 'QSelect/description/adresse',
		));

		$f->add('text', 'ville_avant');
		$f->add('city', 'ville');
		$f->add('text', 'ville_apres');

		$f->add('textarea', 'email_list');

		$f->add('text', 'tel_portable');
		$f->add('text', 'tel_fixe');
		$f->add('text', 'tel_fax');

		$send->attach(
			'adresse', $this->mandatoryAdresse ? 'Veuillez renseigner une adresse' : '', '',
			'description', '', '',
			'ville_avant', '', '',
			'ville', $this->mandatoryAdresse ? 'Veuillez renseigner une ville' : '', '',
			'ville_apres', '', '',
			'email_list', '', '',
			'tel_portable', '', '',
			'tel_fixe', '', '',
			'tel_fax', '', ''
		);


		$action = array(0 => 'Confirmer', 1 => 'Supprimer');
		$this->confirmed && $action[-1] = 'À vérifier';

		$f->add('select', 'adresse_is_obsolete', array(
			'firstItem' => '---',
			'item' => $action
		));

		$confirm = $f->add('submit', 'adresse_confirm');
		$confirm->attach('adresse_is_obsolete', 'Quelle action effectuer sur les adresses sélectionnées ?', '');

		if ($confirm->isOn())
		{
			$this->saveAdresses($confirm->getData());
			p::redirect();
		}
	}


	protected function save($data)
	{
		$this->saveFormContact($data);
		$data['email']   && $this->saveFormEmail($data);
		$data['adresse'] && $this->saveFormAdresse($data);

		return 'user/edit';
	}

	protected function saveFormContact($data)
	{
		$this->contact->save($data);
	}

	protected function saveFormEmail($data)
	{
		$data['is_active'] = $this->mandatoryEmail;

		preg_match_all("'" . FILTER::EMAIL_RX . "'", $data['email'], $email);

		foreach ($email[0] as $email)
		{
			$data['email'] = $email;
			$this->email->save($data);
			unset($data['is_active']);
		}
	}

	protected function saveFormAdresse($data)
	{
		$adresse_id = isset($this->data->adresse_id) ? $this->data->adresse_id : 0;

		$data['is_active'] = $this->mandatoryAdresse;

		if ($data['email_list'])
		{
			preg_match_all("'" . FILTER::EMAIL_RX . "'", $data['email_list'], $email);

			$data['email_list'] = '';

			foreach ($email[0] as $email)
			{
				$data['email_list'] .= $email . "\n";
				$this->email->save(array('email' => $email));
			}
		}

		$this->adresse->save($data, null, $adresse_id);
	}

	protected function saveEmails($data)
	{
		$this->saveContactInfo('email', $data);
	}

	protected function saveAdresses($data)
	{
		$this->saveContactInfo('adresse', $data);
	}

	protected function saveContactInfo($type, $data)
	{
		$ids = array_map('intval', (array) @$_POST[$type . '_id']) + array(0);

		$sql = "SELECT {$type}_id AS id
				FROM contact_{$type}
				WHERE {$type}_id IN (" . implode(',', $ids) . ")
					AND contact_id={$this->contact_id} AND is_obsolete<=0 AND contact_data!=''";
		$result = DB()->query($sql);
		while ($row = $result->fetchRow())
		{
			$this->{$type}->save(array('is_obsolete' => $data[$type . '_is_obsolete']), null, $row->id);
		}
	}
}
