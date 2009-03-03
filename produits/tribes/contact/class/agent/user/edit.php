<?php

class extends agent_registration
{
	const

	PHOTO_WIDTH  = 128,
	PHOTO_HEIGHT = 128;

	protected

	$maxage = 0,
	$requiredAuth = true,
	$loginField = false,

	$photoField,

	$contact_id,
	$confirmed = false,
	$contact = false,
	$email = false,
	$adresse = false,
	$activite = false,

	$emails,
	$adresses,
	$activites,
	$deletedEmail = array();


	function control()
	{
		parent::control();

		empty($this->contact_id) && $this->contact_id = $this->connected_id;

		$this->contact = new tribes_contact($this->contact_id, $this->confirmed);

		$this->data = (array) $this->data;
		$this->data += $this->contact->fetchRow('contact_id, login, contact_confirmed, admin_confirmed, contact_modified, photo_token, contact_data');

		$this->email    = new tribes_email($this->contact_id, $this->confirmed);
		$this->adresse  = new tribes_adresse($this->contact_id, $this->confirmed);
		$this->activite = new tribes_activite($this->contact_id, $this->confirmed);

		$this->data = (object) $this->data;
		$this->data->contact_id =& $this->contact_id;
	}

	function compose($o)
	{
		$o->contact_id = $this->contact_id;

		$o->is_admin_confirmed = $this->data->admin_confirmed > $this->data->contact_modified;

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$o = parent::composeForm($o, $f, $send);

		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);

		return $o;
	}

	protected function formIsOk($f)
	{
		$this->loginField && $this->isLoginCollision($f);

		return parent::formIsOk($f);
	}

	protected function composeContact($o, $f, $send)
	{
		$o = parent::composeContact($o, $f, $send);

		$f->add('text', 'nom_etudiant', self::$altern_case_rx);
		$f->add('text', 'nom_usuel'   , self::$altern_case_rx);
		$f->add('text', 'prenom_usuel', self::$altern_case_rx);
		$f->add('QSelect', 'conjoint_contact_id', array(
			'src' => 'QSelect/login',
		));

		$send->attach(
			'nom_etudiant', "Veuillez renseigner le nom d'étudiant", self::$altern_case_msg,
			'nom_usuel'   , "Veuillez renseigner le nom usuel"     , self::$altern_case_msg,
			'prenom_usuel', "Veuillez renseigner le prénom usuel"  , self::$altern_case_msg,
			'conjoint_contact_id', '', ''
		);

		if ($this->loginField)
		{
			$f->add('text', 'login', '[a-z]+-?[a-z]+\.[a-z]+-?[a-z]+');

			$send->attach('login', 'Veuillez saisir un identifiant', 'Veuillez saisir un identifiant valide');
		}

		$o = $this->composePhoto($o, $f, $send);

		return $o;
	}

	protected function composeEmail($o, $f, $send)
	{
		$this->emails = $o->emails = new loop_edit_user_email($f, $this->contact_id, $send);

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
			$f->add('check', 'del_photo', array(
				'item'     => array(1 => 'Supprimer cette photo'),
				'multiple' => true,
				'isdata'   => true,
			));

			$send->attach('del_photo', '', '');
		}

		$this->photoField = $f->add('file', 'photo', array('valid' => 'image', null, array('jpg','gif','png')));

		$send->attach('photo', '', "Format d'image non valide");

		return $o;
	}

	protected function composeAdresse($o, $f, $send)
	{
		$this->adresses = $o->adresses = new loop_edit_user_adresse($f, $this->contact_id, $send);

		return $o;
	}

	protected function composeActivite($o, $f, $send)
	{
		$this->activites = $o->activites = new loop_edit_user_activite($f, $this->contact_id, $send);

		return $o;
	}


	protected function save($data)
	{
		$this->saveContact($data);
		$this->saveEmail($data);
		$this->saveAdresse($data);
		$this->saveActivite($data);

		return 'user/edit';
	}

	protected function saveContact($data)
	{
		if (!empty($data['del_photo']))
		{
			$file = patchworkPath('data/photo/') . $this->data->photo_token;

			if (file_exists($file . '.contact.jpg')) unlink($file . '.contact.jpg');
			else $data['photo_token'] = p::strongid(8);
		}

		$this->contact->save($data);

		$this->savePhoto();
	}

	protected function saveEmail($data)
	{
		$counter = 0;

		while ($b = $this->emails->loop())
		{
			if (empty($b->deleted) && $b->f_email->getStatus())
			{
				$a = array(
					'email' => $b->f_email->getDbValue(),
				);

				if ('' !== implode('', $a))
				{
					$a += array(
						'contact_id' => $this->contact_id,
						'sort_key'   => ++$counter,
					);

					!$counter && $a->is_active = true;

					$this->email->save($a, null, $b->id);
				}
				else $b->deleted = true;
			}

			if (!empty($b->deleted) && $b->id)
			{
				$this->deletedEmail[$b->email] = 1;
				$this->email->delete($b->id);
			}
		}
	}

	protected function saveAdresse($data)
	{
		$counter = 0;

		while ($b = $this->adresses->loop())
		{
			if (isset($b->f_decision) ? $b->f_decision->getValue() : empty($b->deleted))
			{
				$a = array(
					'adresse'      => $b->f_adresse->getDbValue(),
					'description'  => $b->f_description->getDbValue(),
					'ville_avant'  => $b->f_ville_avant->getDbValue(),
					'ville'        => $b->f_ville->getDbValue(),
					'ville_apres'  => $b->f_ville_apres->getDbValue(),
					'pays'         => $b->f_pays->getDbValue(),
					'email_list'   => $b->f_email_list->getDbValue(),
					'tel_portable' => $b->f_tel_portable->getDbValue(),
					'tel_fixe'     => $b->f_tel_fixe->getDbValue(),
					'tel_fax'      => $b->f_tel_fax->getDbValue(),
					'is_shared'    => $b->f_is_shared->getDbValue(),
				);

				if ('' !== $a['email_list'])
				{
					preg_match_all("'" . FILTER::EMAIL_RX . "'", $a['email_list'], $email);

					$a['email_list'] = '';

					foreach ($email[0] as $email)
					{
						if (isset($this->deletedEmail[strtolower($email)])) continue;

						$a['email_list'] .= $email . "\n";
						$this->email->save(array('email' => $email));
					}
				}

				if ('' !== implode('', $a))
				{
					$a += array(
						'contact_id' => $this->contact_id,
						'is_active'  => !$counter,
						'sort_key'   => ++$counter,
					);

					isset($data['contact_confirmed']) && $a['contact_confirmed'] = $data['contact_confirmed'];

					$this->adresse->save($a, null, $b->id);
				}
				else $b->deleted = true;
			}

			if (!empty($b->deleted) && $b->id)
			{
				$this->adresse->delete($b->id);
			}
		}
	}

	protected function saveActivite($data)
	{
		$counter = 0;

		while ($b = $this->activites->loop())
		{
			if (isset($b->f_decision) ? $b->f_decision->getValue() : empty($b->deleted))
			{
				$a = array(
					'organisation'  => $b->f_organisation->getDbValue(),
					'service'       => $b->f_service->getDbValue(),
					'fonction'      => $b->f_fonction->getDbValue(),
					'secteur'       => $b->f_secteur->getDbValue(),
					'date_debut'    => $b->f_date_debut->getDbValue(),
					'date_fin'      => $b->f_date_fin->getDbValue(),
					'adresse_id'    => $b->f_adresse_id->getDbValue(),	
					'site_web'      => $b->f_site_web->getDbValue(),
					'keyword'       => $b->f_keyword->getDbValue(),
					'is_shared'     => $b->f_is_shared->getDbValue(),
				);

				if ('' !== implode('', $a))
				{
					$a += array(
						'contact_id' => $this->contact_id,
						'sort_key'   => ++$counter,
					);

					isset($data['contact_confirmed']) && $a['contact_confirmed'] = $data['contact_confirmed'];

					$this->activite->save($a, null, $b->id);
				}
				else $b->deleted = true;
			}

			if (!empty($b->deleted) && $b->id)
			{
				$this->activite->delete($b->id);
			}
		}
	}

	protected function savePhoto()
	{
		if (isset($this->photoField) && $file = $this->photoField->getValue())
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

			$this->confirmed || $this->contact->updateContactModified($this->contact_id);
		}

		if ($this->confirmed)
		{
			$file = patchworkPath('data/photo/');

			$photo_token = p::strongid(8);

			@rename($file . $this->data->photo_token . '.contact.jpg', $file . $photo_token . '.jpg');

			$this->contact->save(array('photo_token' => $photo_token), 'user/photo');
		}
	}

	protected function isLoginCollision($f)
	{
		$d = $f->getElement('login');

		$sql = str_replace('-', '', $d->getValue());
		$sql = "SELECT 1
				FROM contact_alias
				WHERE alias='{$sql}'
					AND contact_id!={$this->contact_id}";
		if (DB()->queryOne($sql))
		{
			$d->setError('Identifiant déjà utilisé');
			return false;
		}

		return true;
	}
}
