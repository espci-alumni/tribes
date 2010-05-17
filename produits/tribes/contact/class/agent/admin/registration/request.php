<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

	const PENDING_PERIOD = '3 DAY';

	protected static

	$mergeTableInsert = array(),
	$mergeTableUpdate = array();


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
			$this->data->login = tribes::makeIdentifier($this->data->prenom_civil, "- 'a-z")
			             . '.' . tribes::makeIdentifier($this->data->nom_usuel   , "- 'a-z");
			$this->data->login = preg_replace("/[- ']+/", '-', $this->data->login);
		}

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

		$o->f_send->attach('doublon_contact_id', 'Fusionner avec : merci de choisir un des items proposés', '');

		return $o;
	}

	protected function composeForm($o, $f, $send)
	{
		if (!empty($this->data->login))
		{
			$o = $this->composeLogin($o, $f, $send);
		}

		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send, -1);

		$f->add('textarea', 'message');

		$send->attach('message', '', '');

		return $o;
	}

	protected function composeLogin($o, $f, $send)
	{
		$o = parent::composeLogin($o, $f, $send);

		$send->getStatus() || $this->loginField->setError("Attention, identifiant déjà utilisé");

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

		return true;
	}

	protected function composeEmail($o, $f, $send)
	{
		return agent_registration::composeEmail($o, $f, $send);
	}

	protected function save($data)
	{
		if ($this->doublon_contact_id)
		{
			// XXX TODO:
			// Ce changement de token désactive de fait le formulaire registration/confirmation,
			// mais semble nécessaire pour éviter la fuite des token dans certaines conditions.
			// N'existe-t-il pas un moyen de concilier les deux : sécurité du token et parallélisme des saisies ?

			$this->data->token = p::strongid(8);

			$this->saveContact($data);
			$this->saveEmail($data);
			$this->saveAdresse($data);

			if ($this->doublon_contact_id != $this->data->contact_id)
			{
				self::mergeContacts($this->contact_id, $this->doublon_contact_id);
			}

			$sql = "SELECT contact_id, login, user, nom_usuel, prenom_usuel
				FROM contact_contact
				WHERE contact_id={$this->doublon_contact_id}";
			if ($sql = DB()->queryRow($sql))
			{
				$sql->email = $sql->login . $CONFIG['tribes.emailDomain'];

				$this->createAccount($sql);
			}
		}
		else
		{
			$data = array(
				'email' => $this->data->email,
				'token' => '',
				'statut_inscription' => '',
				'message' => $data['message'],
			);

			$this->contact->save($data, 'registration/refused', $this->contact_id);
		}

		return array('admin/registration/requests', true);
	}

	protected function saveContact($data)
	{
		parent::saveContact($data + array(
			'contact_confirmed' => true,
			'is_active' => 1,
			'statut_inscription' => 'accepted',
			'token' => $this->data->token,
			'token_expires' => 'NOW() + INTERVAL ' . self::PENDING_PERIOD,
		));
	}

	protected function saveEmail($data)
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


	static function __constructStatic()
	{
		$a = array(
			'is_active'   => "IF(VALUES(is_active)=1 OR is_active=1,1,0)",
			'is_obsolete' => "IF(VALUES(is_obsolete)=1 OR is_obsolete=1,1,IF(VALUES(is_obsolete)=-1 OR is_obsolete=-1,-1,0))",
		);

		self::$mergeTableInsert = array(
			'contact_email'   => array('email_id',   $a),
			'contact_adresse' => array('adresse_id', $a),
			'contact_contact' => array('contact_id', $a + array(
				'statut_inscription' => "IF(VALUES(statut_inscription)='accepted' OR statut_inscription='accepted','accepted',IF(VALUES(statut_inscription)='' AND statut_inscription='','','demande'))"
			))
		);

		self::$mergeTableUpdate = array(
			'contact_historique' => array('origine_contact_id' => "IF(origine_contact_id=%d,%d,origine_contact_id)"),
			'contact_alias' => array(),
		);
	}

	static function mergeContacts($from_contact_id, $to_contact_id)
	{
		$db = DB();

		foreach (self::$mergeTableInsert as $table => $info)
		{
			$sql = "SELECT * FROM {$table} WHERE contact_id={$from_contact_id}";
			$result = $db->query($sql);
			while ($from = (array) $result->fetchRow())
			{
				$sql = "DELETE FROM {$table} WHERE {$info[0]}={$from[$info[0]]}";
				$db->exec($sql);

				$from['contact_id'] = $to_contact_id;
				$from = array_map(array($db, 'quote'), $from);

				$sql = "INSERT IGNORE INTO {$table} (" . implode(',', array_keys($from)) . ")
						VALUES (" . implode(',', $from) . ")";

				$from = $info[1] + $from;
				$sql .= "ON DUPLICATE KEY UPDATE contact_id={$to_contact_id}";
				foreach ($from as $k => $v) if ("''" !== $v) $sql .= ",{$k}=" . sprintf($v, $from_contact_id, $to_contact_id);

				$db->exec($sql);
			}
		}

		foreach (self::$mergeTableUpdate as $table => $info)
		{
			$sql = "UPDATE IGNORE {$table}
					SET contact_id={$to_contact_id}";
			foreach ($info as $k => $v) $sql .= ",{$k}=" . sprintf($v, $from_contact_id, $to_contact_id);
			$sql .= " WHERE contact_id={$from_contact_id}";
			$db->exec($sql);

			$sql = "DELETE FROM {$table}
					WHERE contact_id={$from_contact_id}";
			$db->exec($sql);
		}

		notification::send('contact/fusion', array('contact_id' => $to_contact_id));
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

	protected function createAccount($contact)
	{
	}
}
