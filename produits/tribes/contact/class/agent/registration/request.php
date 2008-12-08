<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[A-Za-z0-9]{8}', 'email:i:1', 'adresse:i:1');

	const PENDING_PERIOD = '3 DAY';

	protected

	$action = array('confirm' => 'Confirmer', 'delete' => 'Supprimer', 'tocheck' => 'A verifier'),
	$doublon_contact_id = 0,
	$email;

	function control()
	{
		$sql = "SELECT contact_id
				FROM contact_contact
				WHERE password_token='{$this->get->__1__}'
					AND statut_inscription='demande'";
		$this->get->contact = DB()->queryOne($sql);

		$sql = "SELECT email
				FROM contact_email
				WHERE token='{$this->get->__1__}'";
		$this->email = DB()->queryOne($sql);

		parent::control();

		$this->data->password_token = $this->get->__1__;
	}

	function compose($o)
	{
		$o = parent::compose($o);
		$f = $o->form;

		$doublon_contact_items = self::buildDoublonData($f, clone $this->data);
		$doublon_contact_items = tribes::getDoublonSuggestions($this->data->contact_id, $doublon_contact_items);
		$doublon_contact_items += array(
			$this->data->contact_id => '(ajouter un nouveau nom au fichier)',
			0 => '(refuser la demande)'
		);

		$f->add('check', 'doublon_contact_id', array('item' => $doublon_contact_items));
		$f->add('submit', 'updateDoublons');

		$o->f_send->attach('doublon_contact_id', 'Merci de choisir doublon_contact_id', '');

		return $o;
	}

	protected function composeForm($f, $send)
	{
		parent::composeForm($f, $send);

		$f->add('textarea', 'message');

		$send->attach('message', '', '');
	}

	protected function formIsOk($f)
	{
		if (!isset($_POST['f_doublon_contact_id'])) return false;

		$d = (int) $_POST['f_doublon_contact_id'];

		if ($d === 0) return parent::formIsOk($f);
		if ($d  <  0) return false;

		$sql = "SELECT 1 FROM contact_contact WHERE contact_id={$d}";
		if (!DB()->queryOne($sql)) return false;

		$this->doublon_contact_id = $d;

		return parent::formIsOk($f);
	}

	protected function save($data)
	{
		//XXX parent::save($data);

		$db = DB();

		$message = $data['message'];

		if ($this->doublon_contact_id)
		{
			if ($this->doublon_contact_id != $this->data->contact_id)
			{
				self::mergeContacts($this->data->contact_id, $this->doublon_contact_id);
				$this->data->contact_id = $this->doublon_contact_id;
			}

			$metadata = array(
				'statut_inscription' => "'accepted'",
				'login' => $db->quote(tribes::getLogin($this->data->contact_id, $data)),
				'password_token_expires' => 'NOW() + INTERVAL ' . self::PENDING_PERIOD
			);

			tribes::newInstance('contact', $this->data->contact_id, 1)->update($data, $metadata);

			if ($data['email'])
			{
				$metadata = array(
					'origine' => "'admin'",
					'token' => "'" . $this->data->password_token . "'",
					'token_expires' => 'NOW() + INTERVAL ' . tribes::PENDING_PERIOD
				);
				tribes::newInstance('email', $this->data->contact_id, 1)->insert($data, $metadata);
			}

			if (isset($this->data->adresse))
			{
				tribes::newInstance('adresse', $this->data->contact_id, 1, $this->data->adresse_id)->update($data, array());
			}
			else if (isset($data['adresse']))
			{
				tribes::newInstance('adresse', $this->data->contact_id, 1)->insert($data, array());
			}

		}
		else
		{
			$metadata = array('password_token' => 'NULL', 'statut_inscription' => "''");

			tribes::newInstance('contact', $this->data->contact_id, 1)->update($data, $metadata);
		}

		notification::send(
			"registration/" . ($this->doublon_contact_id ? 'accepted' : 'refused'),
			array(
				'contact_id' => $this->data->contact_id,
				'email.To'   => $this->email,
				'token'      => $this->data->password_token,
				'message'    => $message,
			)
		);

		return array('registration/requests', true);
	}

	static function mergeContacts($from_contact_id, $to_contact_id)
	{
		$db = DB();

		$table = array(
			'is_active'   => "IF(VALUES(is_active)=1 OR is_active=1,1,0)",
			'is_obsolete' => "IF(VALUES(is_obsolete)=1 OR is_obsolete=1,1,IF(VALUES(is_obsolete)=-1 OR is_obsolete=-1,-1,0))",
		);

		$table = array(
			'email'   => array('email_id',   $table),
			'adresse' => array('adresse_id', $table),
			'contact' => array('contact_id', $table + array(
				'statut_inscription' => "IF(VALUES(statut_inscription)='accepted' OR statut_inscription='accepted','accepted',IF(VALUES(statut_inscription)='' AND statut_inscription='','','demande'))"
			)),
		);

		foreach ($table as $table => $info)
		{
			$sql = "SELECT * FROM contact_{$table} WHERE contact_id={$from_contact_id}";
			$result = $db->query($sql);
			while ($from = (array) $result->fetchRow())
			{
				$sql = "DELETE FROM contact_{$table} WHERE {$info[0]}={$from[$info[0]]}";
				$db->exec($sql);

				$from['contact_id'] = $to_contact_id;
				$from = array_map(array($db, 'quote'), $from);

				$sql = "INSERT IGNORE INTO contact_{$table} (" . implode(',', array_keys($from)) . ")
						VALUES (" . implode(',', $from) . ")";

				$from = $info[1] + $from;
				$sql .= "ON DUPLICATE KEY UPDATE contact_id={$to_contact_id}";
				foreach ($from as $k => $v) if ("''" !== $v) $sql .= ",{$k}={$v}";

				$db->exec($sql);
			}
		}


		$table = array(
			'historique' => array('origine_contact_id' => $to_contact_id),
			'alias' => array(),
			'categorie' => array(),
		);

		foreach ($table as $table => $info)
		{
			$sql = "UPDATE IGNORE contact_{$table}
					SET contact_id={$to_contact_id}";
			foreach ($info as $k => $v) $sql .= ",{$k}={$v}";
			$sql .= " WHERE contact_id={$from_contact_id}";
			$db->exec($sql);

			$sql = "DELETE FROM contact_{$table}
					WHERE contact_id={$from_contact_id}";
			$db->exec($sql);
		}
	}

	protected static function buildDoublonData($f, $data)
	{
		$data->nom_civil    = $f->getElement('nom_civil')->getValue();
		$data->prenom_civil = $f->getElement('prenom_civil')->getValue();

		return $data;
	}

	protected function saveEmails($data)
	{
		$email = array_map('intval', (array) @$_POST['email_id']) + array(0);

		switch ($data['email_action'])
		{
		case 'confirm': $sql = 'admin_confirmed=NOW()'; break;
		case 'delete' : $sql = 'is_obsolete=1'; break;
		case 'tocheck': $sql = 'is_obsolete=-1'; break;
		}

		$sql = "UPDATE contact_email
				SET {$sql}
				WHERE email_id IN (" . implode(',', $email) . ")";
		DB()->exec($sql);
	}

	protected function saveAdresses($data)
	{
		$adresse = array_map('intval', (array) @$_POST['adresse_id']) + array(0);

		switch ($data['adresse_action'])
		{
		case 'confirm': $sql = 'admin_confirmed=NOW()'; break;
		case 'delete' : $sql = 'is_obsolete=1'; break;
		case 'tocheck': $sql = 'is_obsolete=-1'; break;
		}

		$sql = "UPDATE contact_adresse
				SET {$sql}
				WHERE adresse_id IN (" . implode(',', $adresse) . ")";
		DB()->exec($sql);
	}
}
