<?php

class extends agent_registration
{
	const

	PHOTO_WIDTH  = 128,
	PHOTO_HEIGHT = 128;

	public $get = array('adresse:i:1');

	protected

	$maxage = 0,
	$requiredAuth = true,
	$mandatoryEmail   = false,
	$mandatoryAdresse = false,

	$photoField,

	$contact_id,
	$confirmed = false,
	$contact = false,
	$email = false,
	$adresse = false;


	function control()
	{
		parent::control();

		empty($this->contact_id) && $this->contact_id = $this->connected_id;

		$this->contact = new tribes_contact($this->contact_id, $this->confirmed);

		$this->data = (array) $this->data;
		$this->data += $this->contact->fetchRow('contact_id, contact_confirmed, admin_confirmed, photo_token, contact_data');

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


	protected function composeForm($o, $f, $send)
	{
		$o->contact_id = $this->contact_id;

		$o->is_admin_confirmed = $this->data->admin_confirmed > $this->data->contact_confirmed;

		$o = parent::composeForm($o, $f, $send);

		$o = $this->composePhoto($o, $f, $send);
		$o = $this->composeFormAdresse($o, $f, $send);

		return $o;
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
			$f->getElement('ville')->setError('Veuillez renseigner une ville');
			return false;
		}

		if ($this->confirmed && $f->getElement('ville')->getStatus())
		{
			if (!(int) $f->getElement('ville')->getDbValue())
			{
				$f->getElement('ville')->setError('Veuillez renseigner une ville connue');
				return false;
			}
		}

		return parent::formIsOk($f);
	}

	protected function composeFormContact($o, $f, $send)
	{
		$o = parent::composeFormContact($o, $f, $send);

		$f->add('text', 'nom_etudiant', self::$altern_case_rx);
		$f->add('text', 'nom_usuel',    self::$altern_case_rx);
		$f->add('text', 'prenom_usuel', self::$altern_case_rx);

		$send->attach(
			'nom_etudiant', "Veuillez renseigner le nom d'étudiant", self::$altern_case_msg,
			'nom_usuel',    "Veuillez renseigner le nom usuel",      self::$altern_case_msg,
			'prenom_usuel', "Veuillez renseigner le prénom usuel",   self::$altern_case_msg
		);

		return $o;
	}

	protected function composeFormEmail($o, $f, $send)
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

		$o->emails = new loop_user_edit_email($this->contact_id, $f);

		return $o;
	}

	protected function composePhoto($o, $f, $send)
	{
		$o->photo_token = $this->data->photo_token;

		$file = patchworkPath('data/photo/') . $this->data->photo_token;

		switch (true)
		{
		case file_exists($file . '.contact.jpg'):
			$o->hasPhoto = true;
			$file .= '.contact.jpg';
			break;

		case file_exists($file . '.jpg'):
			$o->hasPhoto = true;
			$file .= '.jpg';
			break;

		default:
			$o->hasPhoto = false;
		}

		if ($o->hasPhoto)
		{
			$delete = $f->add('submit', 'delete');

			if ($delete->isOn())
			{
				unlink($file);
				p::redirect();
			}
		}

		$this->photoField = $f->add('file', 'photo', array('valid' => 'image', null, array('jpg','gif','png')));
		
		$send->attach('photo', '', "Format d'image non valide");

		return $o;
	}

	protected function composeFormAdresse($o, $f, $send)
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

		$o->adresses = new loop_user_edit_adresse($this->contact_id, $f);

		return $o;
	}


	protected function save($data)
	{
		$this->saveFormContact($data);
		$this->savePhoto();
		$data['email']   && $this->saveFormEmail($data);
		$data['adresse'] && $this->saveFormAdresse($data);

		return '';
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

	protected function savePhoto()
	{
		if ($file = $this->photoField->getValue())
		{
			$th_w = self::PHOTO_WIDTH;
			$th_h = self::PHOTO_HEIGHT;

			list($src_w, $src_h, $src_type) = $file['info'];

			switch ($src_type)
			{
				case 'gif': $src_img = imagecreatefromgif($file['tmp_name']); break;
				case 'jpg': $src_img = imagecreatefromjpeg($file['tmp_name']); break;
				case 'png': $src_img = imagecreatefrompng($file['tmp_name']); break;
				default : return;
			}

			if ($src_w > $src_h) $th_h *= $src_h / $src_w;
			else if ($src_w < $src_h) $th_w *= $src_w / $src_h;

			$th_img = imagecreatetruecolor($th_w, $th_h);
			$bgcolor = imagecolorallocate($th_img, 255, 255, 255);
			imagefilledrectangle($th_img, 0, 0, $th_w, $th_h, $bgcolor);
			imagecopyresampled($th_img, $src_img, 0, 0, 0, 0, $th_w, $th_h, $src_w, $src_h);

			$file = patchworkPath('data/photo/') . $this->data->photo_token . '.contact.jpg';

			imagejpeg($th_img, $file, 90);
		}

		if ($this->confirmed)
		{
			$file = patchworkPath('data/photo/');

			$photo_token = p::strongid(8);

			@unlink($file . $this->data->photo_token . '.jpg');
			@rename($file . $this->data->photo_token . '.contact.jpg', $file . $photo_token . '.jpg');

			$this->contact->save(array('photo_token' => $photo_token), 'user/photo');
		}
	}
}
