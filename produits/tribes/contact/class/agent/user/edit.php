<?php

class extends agent_registration
{
	const

	PHOTO_WIDTH  = 128,
	PHOTO_HEIGHT = 128;

	protected

	$maxage = 0,
	$requiredAuth = true,

	$loginField,
	$photoField,
	$cvField,

	$contact_id,
	$confirmed = false,
	$contact   = false,
	$email     = false,
	$adresse   = false,
	$activite  = false,

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
		$this->data += $this->contact->fetchRow('contact_id, login, contact_confirmed, admin_confirmed, contact_modified, photo_token, cv_token, contact_data');

		$this->email    = new tribes_email($this->contact_id, $this->confirmed);
		$this->adresse  = new tribes_adresse($this->contact_id, $this->confirmed);
		$this->activite = new tribes_activite($this->contact_id, $this->confirmed);

		$this->data = (object) $this->data;
		$this->data->contact_id =& $this->contact_id;
	}

	function compose($o)
	{
		$o->contact_id = $this->contact_id;
		$o->login = $this->data->login;

		$o->is_admin_confirmed = $this->data->admin_confirmed > $this->data->contact_modified;

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$o = $this->composeLogin($o, $f, $send);
		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);
		$o = $this->composeNewPassword($o, $f, $send);
		$o = $this->composePassword($o, $f, $send);

		return $o;
	}

	protected function formIsOk($f)
	{
		$this->loginField && $this->isLoginCollision($f);

		if ($e = $f->getElement('password'))
		{
			if (!p::matchSaltedHash($e->getValue(), s::get('saltedPassword')))
			{
				$e->setError('Mot de passe incorrect');
				return false;
			}
		}

		if ($e = $f->getElement('con_pwd'))
		{
			if ($f->getElement('new_pwd')->getValue() !== $e->getValue())
			{
				$e->setError('Confirmation échouée');
				return false;
			}
		}

		return parent::formIsOk($f);
	}

	protected function composePassword($o, $f, $send)
	{
		$f->add('password', 'password', array('isdata' => false));
		$send->attach('password', 'Veuillez saisir votre mot de passe actuel', '');
		return $o;
	}

	protected function composeNewPassword($o, $f, $send)
	{
		$f->add('password', 'new_pwd');
		$f->add('password', 'con_pwd', array('isdata' => false));

		$send->attach(
			'new_pwd', '', '',
			'con_pwd', '', ''
		);

		return $o;
	}

	protected function composeContact($o, $f, $send)
	{
		$o = parent::composeContact($o, $f, $send);

		$f->add('name', 'nom_etudiant');
		$f->add('name', 'nom_usuel');
		$f->add('name', 'prenom_usuel');
		$f->add('QSelect', 'conjoint_contact_id', array(
			'src' => 'QSelect/login',
		));

		$send->attach(
			'nom_etudiant', "Veuillez renseigner le nom d'étudiant", '',
			'nom_usuel'   , "Veuillez renseigner le nom usuel"     , '',
			'prenom_usuel', "Veuillez renseigner le prénom usuel"  , '',
			'conjoint_contact_id', '', ''
		);

		$o = $this->composePhoto($o, $f, $send);
		$o = $this->composeCv($o, $f, $send);

		return $o;
	}

	protected function composeLogin($o, $f, $send)
	{
		$this->loginField = $f->add('text', 'login', '[a-z]+-?[a-z]+\.[a-z]+-?[a-z]+');
		$send->attach('login', 'Veuillez saisir un identifiant', 'Identifiant non valide');

		return $o;
	}

	protected function composeEmail($o, $f, $send)
	{
		$this->emails = $o->emails = new loop_edit_contact_email($f, $this->contact_id, $send);

		$sql = "SELECT alias
				FROM contact_alias
				WHERE contact_id={$this->contact_id}
				ORDER BY alias";
		$o->alias = new loop_sql($sql);

		return $o;
	}

	protected function composePhoto($o, $f, $send)
	{
		$o->photo_token = $this->data->photo_token;

		$file = patchworkPath('data/photo/') . $this->data->photo_token . '.jpg';

		$o->hasPhoto = file_exists($file) || file_exists($file . '~');

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

	protected function composeCv($o, $f, $send)
	{
		$o->cv_token = $this->data->cv_token;

		$file = patchworkPath('data/cv/') . $this->data->cv_token . '.pdf';

		$o->hasCv = file_exists($file) || file_exists($file . '~');

		if ($o->hasCv)
		{
			$f->add('check', 'del_cv', array(
				'item'     => array(1 => 'Supprimer ce CV'),
				'multiple' => true,
				'isdata'   => true,
			));

			$send->attach('del_cv', '', '');
		}

		$this->cvField = $f->add('file', 'cv');

		$send->attach('cv', '', 'Type de fichier non valide');

		return $o;
	}

	protected function composeAdresse($o, $f, $send)
	{
		$this->adresses = $o->adresses = new loop_edit_contact_adresse($f, $this->contact_id, $send);

		return $o;
	}

	protected function composeActivite($o, $f, $send)
	{
		$this->activites = $o->activites = new loop_edit_contact_activite($f, $this->contact_id, $send);

		return $o;
	}


	protected function save($data)
	{
		$this->saveContact($data);
		$this->saveEmail($data);
		$this->saveAdresse($data);
		$this->saveActivite($data);
		$this->saveNewPassword($data);

		return '';
	}

	protected function saveContact($data)
	{
		$this->savePhoto($data);
		$this->saveCv($data);

		$this->contact->save($data);
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

	protected function saveNewPassword($data)
	{
		if ('' !== $data['new_pwd'])
		{
			$data = array(
				'password' => p::saltedHash($data['new_pwd']),
				'token'    => '',
			);

			$this->contact->save($data);
		}
	}

	protected function savePhoto(&$data)
	{
		if (!empty($data['del_photo']))
		{
			$file = patchworkPath('data/photo/') . $this->data->photo_token . '.jpg';

			if (file_exists($file . '~')) unlink($file . '~');
			else $data['photo_token'] = p::strongid(8);
		}

		if (!$this->data->photo_token)
		{
			$this->data->photo_token = $data['photo_token'] = p::strongid(8);
		}

		if (isset($this->photoField) && $this->photoField->getStatus())
		{
			$file = $this->photoField->getValue();

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

			$file = patchworkPath('data/photo/') . $this->data->photo_token . '.jpg~';

			imagejpeg($th_img, $file, 90);

			$this->confirmed || $this->contact->updateContactModified($this->contact_id);
		}

		if ($this->confirmed)
		{
			$file = patchworkPath('data/photo/');

			$photo_token = p::strongid(8);

			if (@rename($file . $this->data->photo_token . '.jpg~', $file . $photo_token . '.jpg'))
			{
				notification::send('user/photo', array(
					'contact_id'  => $this->contact_id,
					'photo_token' => $photo_token,
				));

				$data['photo_token'] = $photo_token;
			}
		}
	}

	protected function saveCv(&$data)
	{
		if (!empty($data['del_cv']))
		{
			$file = patchworkPath('data/cv/') . $this->data->cv_token . '.pdf';

			if (file_exists($file . '~')) unlink($file . '~');
			else $data['cv_token'] = p::strongid(8);
		}

		if (!$this->data->cv_token)
		{
			$this->data->cv_token = $data['cv_token'] = p::strongid(8);
		}

		$cv_text = '';

		if (isset($this->cvField) && $this->cvField->getStatus())
		{
			if ($file = $this->cvField->getValue())
			{
				$cv_text = new converter_txt_pdf;

				if ($cv_text = $cv_text->convertFile($file['tmp_name']))
				{
					move_uploaded_file($file['tmp_name'], patchworkPath('data/cv/') . $this->data->cv_token . '.pdf~');

					$this->confirmed || $this->contact->updateContactModified($this->contact_id);
				}
			}
		}

		if ($this->confirmed)
		{
			$file = patchworkPath('data/cv/');

			$cv_token = p::strongid(8);

			if (@rename($file . $this->data->cv_token . '.pdf~', $file . $cv_token . '.pdf'))
			{
				if (!$cv_text)
				{
					$cv_text = new converter_txt_pdf;
					$cv_text = $cv_text->convertFile($file . $cv_token . '.pdf');
				}

				notification::send('user/cv', array(
					'contact_id' => $this->contact_id,
					'cv_token'   => $cv_token,
				));

				$data['cv_token'] = $cv_token;
				$data['cv_text']  = $cv_text;
			}
		}
	}

	protected function isLoginCollision($f)
	{
		$sql = str_replace('-', '', $this->loginField->getValue());
		$sql = "SELECT 1
				FROM contact_alias
				WHERE alias='{$sql}'
					AND contact_id!={$this->contact_id}";
		if (DB()->queryOne($sql))
		{
			$this->loginField->setError('Identifiant déjà utilisé');
			return false;
		}

		return true;
	}
}
