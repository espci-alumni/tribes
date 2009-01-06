<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[A-Za-z0-9]{8}', 'adresse:i:1');

	const PENDING_PERIOD = '3 DAY';

	protected

	$requiredAuth = 'admin',
	$confirmed = true,
	$doublon_contact_id = 0;


	function control()
	{
		$sql = "SELECT contact_id
				FROM contact_contact
				WHERE token='{$this->get->__1__}'
					AND statut_inscription='demande'";
		$this->contact_id = DB()->queryOne($sql);
		$this->contact_id || p::redirect('error/token');

		$sql = "SELECT email
				FROM contact_email
				WHERE token='{$this->get->__1__}'";
		$this->data['email'] = DB()->queryOne($sql);

		parent::control();

		if ($this->isAliasCollision())
		{
			$this->data->login = tribes::makeIdentifier($this->data->prenom_civil, '-a-z')
				. '.' . tribes::makeIdentifier($this->data->nom_usuel, '-a-z');
		}

		$this->mandatoryEmail = true;
		$this->mandatoryAdresse = false;

		$this->data->token = $this->get->__1__;
	}

	function compose($o)
	{
		$o = parent::compose($o);
		$f = $o->form;

		$doublon_contact_items = self::buildDoublonData($f, clone $this->data);
		$doublon_contact_items = tribes::getDoublonSuggestions($this->contact_id, $doublon_contact_items);
		$doublon_contact_items += array(
			$this->contact_id => '(ajouter un nouveau nom au fichier)',
			0 => '(refuser la demande)'
		);

		$f->add('check', 'doublon_contact_id', array('item' => $doublon_contact_items));
		$f->add('submit', 'updateDoublons');

		$o->f_send->attach('doublon_contact_id', 'Merci de choisir doublon_contact_id', '');

		return $o;
	}

	protected function composeForm($o, $f, $send)
	{
		$o = parent::composeForm($o, $f, $send);

		$f->add('textarea', 'message');

		$send->attach('message', '', '');

		return $o;
	}

	protected function composeFormContact($o, $f, $send)
	{
		$o = parent::composeFormContact($o, $f, $send);

		if (isset($this->data->login))
		{
			$f = $f->add('text', 'login', '[-a-z]{2,}\.[-a-z]{2,}');

			$send->attach('login', 'Veuillez saisir un identifiant', 'Veuillez saisir un identifiant valide');

			$send->getStatus() || $f->setError("Attention, identifiant déjà utilisé");
		}

		return $o;
	}

	protected function formIsOk($f)
	{
		if (!parent::formIsOk($f)) return false;


		if (!isset($_POST['f_doublon_contact_id'])) return false;

		$d = (int) $_POST['f_doublon_contact_id'];
		if ($d < 0) return false;

		if ($d === 0) return true; // Rejet de la demande

		$db = DB();

		$sql = "SELECT 1 FROM contact_contact WHERE contact_id={$d}";
		if (!$db->queryOne($sql)) return false;

		$this->doublon_contact_id = $d;


		if (isset($this->data->login))
		{
			$d = $f->getElement('login');

			$sql = str_replace('-', '', $d->getValue());
			$sql = "SELECT 1
					FROM contact_alias
						WHERE alias=" . $db->quote($sql) . "
						AND contact_id!={$this->contact_id}";
			if ($db->queryOne($sql))
			{
				$d->setError('Identifiant déjà utilisé');
				return false;
			}
		}

		return true;
	}

	protected function composeFormEmail($o, $f, $send)
	{
		$f->add('email', 'email');

		$send->attach('email', "Veuillez renseigner un email", '');

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

		return $o;
	}

	protected function save($data)
	{
		if ($this->doublon_contact_id)
		{
			parent::save($data);

			if ($this->doublon_contact_id != $this->data->contact_id)
			{
				self::mergeContacts($this->contact_id, $this->doublon_contact_id);
			}
		}
		else
		{
			$data = array(
				'token' => '',
				'statut_inscription' => '',
			);

			$this->contact->save($data, 'registration/refused', $this->contact_id);
		}

		return array('registration/requests', true);
	}

	protected function saveFormContact($data)
	{
		if (isset($this->data->login))
		{
			$sql = str_replace('-', '', $data['login']);
			$sql = "INSERT IGNORE INTO contact_alias (contact_id, alias)
					VALUES ({$this->contact_id},'{$sql}')";

			if (DB()->exec($sql))
			{
				$sql = "UPDATE contact_contact
						SET login='{$data['login']}'
						WHERE contact_id={$this->contact_id}
							AND login=''";
				DB()->exec($sql);
			}
		}

		parent::saveFormContact($data + array(
			'is_active' => 1,
			'statut_inscription' => 'accepted',
			'token_expires' => 'NOW() + INTERVAL ' . self::PENDING_PERIOD,
		));
	}

	protected function saveFormEmail($data)
	{
		$sql = "UPDATE contact_email
				SET token=NULL
				WHERE token='{$this->data->token}'";
		DB()->exec($sql);

		$data += array(
			'contact_confirmed' => true,
			'is_active' => 1,
			'token' => $this->data->token,
			'token_expires' => 'NOW() + INTERVAL ' . self::PENDING_PERIOD,
		);

		$this->email->save($data, 'registration/accepted');
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

	protected function isAliasCollision()
	{
		$db = DB();

		for ($i = 0; $i < count(tribes_contact::$alias); ++$i)
		{
			$sql = tribes_contact::$alias[$i];

			$sql = tribes::makeIdentifier($this->data->{$sql[0]})
				. '.' . tribes::makeIdentifier($this->data->{$sql[1]});

			$sql = "SELECT 1
					FROM contact_alias
					WHERE alias='{$sql}'";

			if (!$db->queryOne($sql)) return false;
		}

		return true;
	}
}
