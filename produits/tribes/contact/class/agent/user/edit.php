<?php

class extends agent_registration
{
	public $get = array('email:i:1', 'adresse:i:1', 'contact:i:1' => 9); //XXX enlever "9"

	protected

	$maxage = 0,
	$action = array('confirm' => 'Confirmer', 'delete' => 'Supprimer'),

	$contact_id,
	$mandatoryEmail = false,
	$mandatoryAdresse = false;


	function control()
	{
		parent::control();

		// XXX controle d'accès à ce contact_id là
		$this->contact_id = $this->get->contact;

		$this->data = (array) $this->data;
		$this->data += tribes::newInstance('contact', $this->contact_id, 0)->fetchRow('contact_id');

		if ($this->get->email)
		{
			$data = tribes::newInstance('email', $this->contact_id, 0, $this->get->email)->fetchRow('email_id, is_obsolete, contact_confirmed');
			$data['email_description'] =& $data['description'];
			unset($data['description']);
			$this->data += $data;
		}

		if ($this->get->adresse)
		{
			$data = tribes::newInstance('adresse', $this->contact_id, 0, $this->get->adresse)->fetchRow('adresse_id');
			$data['adresse_description'] =& $data['description'];
			isset($data['pays']) && $data['ville'] .= ', ' . $data['pays'];
			unset($data['description']);
			$this->data += $data;
		}

		$sql = "SELECT 1 FROM contact_email
				WHERE contact_id={$this->contact_id}
					AND is_obsolete=0 AND contact_confirmed
				LIMIT 1";
		$this->mandatoryEmail = DB()->queryOne($sql) ? false : true;

		$sql = "SELECT 1 FROM contact_adresse
				WHERE contact_id={$this->contact_id}
					AND is_obsolete=0 AND contact_confirmed
				LIMIT 1";
		$this->mandatoryAdresse = DB()->queryOne($sql) ? false : true;

		$this->data = (object) $this->data;
	}

	function compose($o)
	{
		$o = parent::compose($o);

		$o->emails   = new loop_user_edit_email($this->data->contact_id, $o->form);
		$o->adresses = new loop_user_edit_adresse($this->data->contact_id, $o->form);

		return $o;
	}

	protected function composeForm($f, $send)
	{
		parent::composeForm($f, $send);

		$this->composeFormAdresse($f, $send);
	}

	protected function composeFormContact($f, $send)
	{
		parent::composeFormContact($f, $send);

		$altern_case_rx = ".*[A-Z][^A-Z\s]+";
		$altern_case_msg = "Merci de respecter minuscules, majuscules et accents pour vos nom et prénom";

		$f->add('text', 'nom_etudiant', $altern_case_rx);
		$f->add('text', 'nom_usuel',    $altern_case_rx);
		$f->add('text', 'prenom_usuel', $altern_case_rx);

		$send->attach(
			'nom_etudiant', '', $altern_case_msg,
			'nom_usuel',    '', $altern_case_msg,
			'prenom_usuel', '', $altern_case_msg
		);
	}

	protected function composeFormEmail($f, $send)
	{
		parent::composeFormEmail($f, $send);

		$f->add('QSelect', 'email_description', array(
			'src' => 'QSelect/description/email',
		));

		$send->attach('email_description', '', '');

		$f->add('select', 'email_action', array(
			'firstItem' => '---',
			'item' => $this->action
		));

		$email_confirm = $f->add('submit', 'email_confirm');
		$email_confirm->attach('email_action', 'Quelle action effectuer sur les emails sélectionnés ?', '');

		if ($email_confirm->isOn())
		{
			$this->saveEmails($email_confirm->getData());
			p::redirect();
		}
	}

	protected function saveEmails($data)
	{
		$email = array_map('intval', (array) @$_POST['email_id']) + array(0);

		$db = DB();

		if ('confirm' === $data['email_action'])
		{
			$sql = "SELECT email_id, email
					FROM contact_email
					WHERE email_id IN (" . implode(',', $email) . ")
						AND (!contact_confirmed OR is_obsolete<0)";
			$result = $db->query($sql);

			while ($row = $result->fetchRow())
			{
				$email = array(
					'token' => "'" . p::strongid(8) . "'",
					'token_expires' => 'NOW()+INTERVAL ' . tribes::PENDING_PERIOD
				);

				tribes::newInstance('email', $this->contact_id, 0, $row->email_id)->update(array('email' => $row->email), $email);
			}
		}
		else if ('delete' === $data['email_action'])
		{
			foreach ($email as $email)
			{
				tribes::newInstance('email', $this->contact_id, 0, $email)->delete();
			}

		}
	}

	protected function composeFormAdresse($f, $send)
	{
		$f->add('textarea', 'adresse');

		$f->add('QSelect', 'adresse_description', array(
			'src' => 'QSelect/description/adresse',
		));

		$f->add('text', 'ville_avant');
		$f->add('city', 'ville');
		$f->add('text', 'ville_apres');

		$f->add('text', 'tel_portable');
		$f->add('text', 'tel_fixe');
		$f->add('text', 'tel_fax');

		$send->attach(
			'adresse', $this->mandatoryAdresse ? 'Veuillez renseigner une adresse' : '', '',
			'adresse_description', '', '',
			'ville_avant', '', '',
			'ville', $this->mandatoryAdresse ? 'Veuillez renseigner une ville' : '', '',
			'ville_apres', '', '',
			'tel_portable', '', '',
			'tel_fixe', '', '',
			'tel_fax', '', ''
		);

		$f->add('select', 'adresse_action', array(
			'firstItem' => '---',
			'item' => $this->action
		));

		$adresse_confirm = $f->add('submit', 'adresse_confirm');
		$adresse_confirm->attach('adresse_action', 'Quelle action effectuer sur les adresses sélectionnées ?', '');

		if ($adresse_confirm->isOn())
		{
			$this->saveAdresses($adresse_confirm->getData());
			p::redirect();
		}
	}

	protected function saveAdresses($data)
	{
		$adresse = array_map('intval', (array) @$_POST['adresse_id']) + array(0);

		$sql = 'confirm' === $data['adresse_action'] ? 'contact_confirmed=NOW()' : 'is_obsolete=1';
		$sql = "UPDATE contact_adresse
				SET {$sql}
				WHERE adresse_id IN (" . implode(',', $adresse) . ")";
		DB()->exec($sql);
	}

	protected function save($data)
	{
		tribes::newInstance('contact', $this->contact_id, 0)->update($data, array());

		if ($data['email'])
		{
			$metadata = array(
				'token' => "'" . p::strongid(8) . "'",
				'token_expires' => 'NOW()+INTERVAL ' . tribes::PENDING_PERIOD
			);

			if (isset($this->data->email_id) && $this->data->email === $data['email'])
			{
				tribes::newInstance('email', $this->contact_id, 0, $this->data->email_id)->update($data, $metadata);
			}
			else
			{
				if (isset($this->data->email_id) && $this->data->email !== $data['email'])
				{
					tribes::newInstance('email', $this->contact_id, 0, $this->data->email_id)->delete();
				}

				tribes::newInstance('email', $this->contact_id, 0)->insert($data, $metadata);
			}
		}

		if ($data['adresse'])
		{
			if (!isset($this->data->adresse_id))
			{
				tribes::newInstance('adresse', $this->contact_id, 0)->insert($data, array());
			}
			else
			{
				$metadata = array('origine' => "'user/edit'");
				tribes::newInstance('adresse', $this->contact_id, 0, $this->data->adresse_id)->update($data, $metadata);
			}
		}

		return 'user/edit';
	}
}
