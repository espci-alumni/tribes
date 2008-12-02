<?php

class extends agent_user_edit
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	const PENDING_PERIOD = '3 DAY';

	protected $doublon_contact_id = 0;

	function control()
	{
		parent::control();

		$sql = "SELECT c.contact_id,
					password_token,
					c.contact_confirmed_data,
					prenom_civil,
					nom_civil,
					date_naissance,
					email
				FROM contact c JOIN contact_email e ON password_token=token
				WHERE password_token='{$this->get->__1__}'
					AND statut_inscription='demande'";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('error/token');

		$this->data = (object) ((array) $this->data + unserialize($this->data->contact_confirmed_data));
	}

	function compose($o)
	{
		$o = parent::compose($o);
		$f = $o->form;

		$doublon_contact_items = self::buildDoublonKey($f, clone $this->data);
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

		$send->attach(
			'message', '', ''
		);
	}

	protected function formIsOk($f)
	{
		if (!isset($_POST['f_doublon_contact_id'])) return false;

		$d = (int) $_POST['f_doublon_contact_id'];

		if ($d === 0) return true;
		if ($d  <  0) return false;

		$sql = "SELECT 1 FROM contact WHERE contact_id={$d}";
		if (!DB()->queryOne($sql)) return false;

		$this->doublon_contact_id = $d;

		return true;
	}

	protected function save($data)
	{
		$db = DB();

		$password_token = $this->data->password_token;

		$adresse = $data['adresse'];
		$message = $data['message'];
		$email   = $data['email'];

		unset($data['adresse'], $data['message'], $data['email']);

		if ($this->doublon_contact_id)
		{
			if ($this->doublon_contact_id != $this->data->contact_id)
			{
				self::mergeContacts($this->data->contact_id, $this->doublon_contact_id);
				$this->data->contact_id = $this->doublon_contact_id;
			}

			$sql = "UPDATE contact
					SET is_active=1,
						admin_confirmed=NOW(),
						statut_inscription='accepted',
						login=" . $db->quote(tribes::getLogin($this->data->contact_id, $data)) . ",";
			$sql .= "password_token_expires=NOW() + INTERVAL " . self::PENDING_PERIOD;
			foreach ($data as $k => $v) $sql .= ",{$k}=" . $db->quote($v);
			$sql .= "WHERE password_token='{$password_token}'";
			$db->exec($sql);

			$sql = "INSERT INTO contact_email
						(contact_id, email, token, origine, is_active, admin_confirmed)
					VALUES ({$this->data->contact_id},'{$email}','{$password_token}', 'admin', 1, NOW())
					ON DUPLICATE KEY UPDATE is_active=1, admin_confirmed=NOW()";
			$db->exec($sql);
		}
		else
		{
			$email = $this->data->email;

			$sql = "UPDATE contact SET password_token=NULL, statut_inscription=''
					WHERE contact_id={$this->data->contact_id}";
			$db->exec($sql);
		}

		notification::send(
			"registration/" . ($this->doublon_contact_id ? 'accepted' : 'refused'),
			array(
				'contact_id'     => $this->data->contact_id,
				'email.To'       => $email,
				'password_token' => $password_token,
				'message'        => $message,
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
			'contact_email'   => array('email_id',   $table),
			'contact_adresse' => array('adresse_id', $table),
			'contact'         => array('contact_id', $table + array(
				'statut_inscription' => "IF(VALUES(statut_inscription)='accepted' OR statut_inscription='accepted','accepted',IF(VALUES(statut_inscription)='' AND statut_inscription='','','demande'))"
			)),
		);

		foreach ($table as $table => $info)
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
				foreach ($from as $k => $v) if ("''" !== $v) $sql .= ",{$k}={$v}";

				$db->exec($sql);
			}
		}


		$table = array(
			'contact_historique' => array('origine_contact_id' => $to_contact_id),
			'contact_alias' => array(),
			'contact_categorie' => array(),
		);

		foreach ($table as $table => $info)
		{
			$sql = "UPDATE IGNORE {$table}
					SET contact_id={$to_contact_id}";
			foreach ($info as $k => $v) $sql .= ",{$k}={$v}";
			$sql .= " WHERE contact_id={$from_contact_id}";
			$db->exec($sql);

			$sql = "DELETE FROM {$table}
					WHERE contact_id={$from_contact_id}";
			$db->exec($sql);
		}
	}

	protected static function buildDoublonKey($f, $data)
	{
		$data->nom_civil    = $f->getElement('nom_civil')->getValue();
		$data->prenom_civil = $f->getElement('prenom_civil')->getValue();

		return $data;
	}
}
