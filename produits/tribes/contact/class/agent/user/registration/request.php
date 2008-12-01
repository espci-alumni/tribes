<?php

class extends agent_user_edit
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	const PENDING_PERIOD = '3 DAY';

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

	protected function composeForm($f, $send)
	{
		parent::composeForm($f, $send);

		$f->add('textarea', 'message');

		$send->attach(
			'message', '', ''
		);

		$doublon_contact_items = self::buildDoublonKey($f, clone $this->data);
		$doublon_contact_items = tribes::getDoublonSuggestions($this->data->contact_id, $doublon_contact_items);
		$doublon_contact_items += array(
			0  => '(ajouter un nouveau nom au fichier)',
			-1 => '(Refuser la demande)'
		);

		$f->add('check', 'doublon_contact_id', array('item' => $doublon_contact_items));

		$f->add('submit', 'updateDoublons');

		$send->attach('doublon_contact_id', 'Merci de choisir doublon_contact_id', '');
	}

	protected function save($data)
	{
		$db = DB();

		$password_token = $this->data->password_token;

		$adresse = $data['adresse'];
		$message = $data['message'];
		$email   = $data['email'];
		$doublon_contact_id = $data['doublon_contact_id'];

		unset($data['adresse'], $data['message'], $data['email'], $data['doublon_contact_id']);

		if ($doublon_contact_id >= 0)
		{
			if ($doublon_contact_id)
			{
				self::mergeContacts($this->data->contact_id, $doublon_contact_id);
				$this->data->contact_id = $doublon_contact_id;
			}

			$sql = "UPDATE contact
					SET is_active=1,
						admin_confirmed=NOW(),
						login=" . $db->quote(tribes::getLogin($this->data->contact_id, $data)) . ",";
			$sql .= "password_token_expires=NOW() + INTERVAL " . self::PENDING_PERIOD;
			foreach ($data as $k => $v) $sql .= ",{$k}=" . $db->quote($v);
			$sql .= "WHERE password_token='{$password_token}'";
			$db->exec($sql);

			$sql = "INSERT IGNORE INTO contact_email
						(contact_id, email, token, origine, is_active, admin_confirmed)
					VALUES ({$this->data->contact_id},'{$email}','{$password_token}', 'admin', 1, NOW())
					ON DUPLICATE KEY UPDATE is_active=1, admin_confirmed=NOW()";E($sql);
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
			"user/registration/" . ($doublon_contact_id >= 0 ? 'accepted' : 'refused'),
			array(
				'contact_id'     => $this->data->contact_id,
				'email.To'       => $email,
				'password_token' => $password_token,
				'message'        => $message,
			)
		);

		return array('user/registration/requests', true);
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
			$ids = array();

			$sql = "SELECT *, {$info[0]} AS id
					FROM {$table}
					WHERE contact_id={$from_contact_id}";
			$result = $db->query($sql);
			while ($from = (array) $result->fetchRow())
			{
				$ids[] = $from['id'];
				unset($from['id']);

				$from['contact_id'] = $to_contact_id;
				$from = array_map(array($db, 'quote'), $from);

				$sql = "INSERT IGNORE INTO {$table} (" . implode(',', array_keys($from)) . ")
						VALUES (" . implode(',', $from) . ")";

				$from = $info[1] + $from;
				$sql .= "ON DUPLICATE KEY UPDATE contact_id={$to_contact_id}";
				foreach ($from as $k => $v) if ("''" !== $v) $sql .= ",{$k}={$v}";

				$db->exec($sql);
			}

			if ($ids)
			{
				$sql = "DELETE FROM {$table} WHERE {$info[0]} IN (" . implode(',', $ids) . ")";
				$db->exec($sql);
			}
		}


		$table = array(
			'contact_historique',
			'contact_alias',
			'contact_categorie',
		);

		foreach ($table as $table)
		{
			$sql = "UPDATE IGNORE {$table}
					SET contact_id={$to_contact_id}
					WHERE contact_id={$from_contact_id}";
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
