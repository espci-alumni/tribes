<?php

class extends tribes_common
{
	protected

	$table = 'contact',

	$dataFields = array(
		'sexe',
		'nom_civil',
		'prenom_civil',
		'nom_usuel',
		'prenom_usuel',
		'nom_etudiant',
		'date_naissance',
		'date_deces',
		'conjoint_contact_id',
	);

	function __construct($contact_id, $confirmed = 0)
	{
		$this->metaFields += array(
			'token'              => 'stringNull',
			'token_expires'      => 'sql',
			'statut_inscription' => 'string',
			'login'              => 'string',
			'password'           => 'string',
			'photo_token'        => 'string',
		);

		parent::__construct($contact_id, $confirmed);

		$contact_id || $this->contact_id = 0;
	}

	function save($data, $message = null, $id = 0)
	{
		if (isset($data['login']))
		{
			W(__METHOD__ . '() input error: $data[\'login\'] must not be set');
			unset($data['login']);
		}

		if ($this->confirmed || !$this->contact_id)
		{
			if ($data['login'] = self::buildLogin($data))
			{
				$data['login'] = $this->buildUniqueLogin($data['login']);
			}
			else
			{
				$this->contact_id || W(__METHOD__ . '() error: unable to build a valid login');

				unset($data['login']);
			}
		}

		$this->contact_id || $data['photo_token'] = p::strongid(8);

		$message = parent::save($data, $message, $this->contact_id);

		if ($this->confirmed && isset($data['login']))
		{
			$sql = "INSERT INTO contact_alias
						(login,contact_id)
					VALUES
						(" . DB()->quote($data['login']) . ",{$this->contact_id})
					ON DUPLICATE KEY UPDATE contact_id={$this->contact_id}";
			DB()->exec($sql);
		}

		return $message;
	}


	static function buildLogin($data)
	{
		$login = false;

		if (   isset($data['prenom_civil'])
			&& isset($data['nom_civil'])
			&& isset($data['date_naissance']))
		{
			$login = tribes::filterIdentifier($data['prenom_civil'])
				. '.' . tribes::filterIdentifier($data['nom_civil'])
				. '.' . $data['date_naissance'];
		}

		return $login;
	}


	protected function buildUniqueLogin($login)
	{
		if ($this->contact_id)
		{
			$sql = "SELECT login
					FROM contact_contact
					WHERE login LIKE " . DB()->quote($login . '%') . "
						AND contact_id={$this->contact_id}";
			if ($sql = DB()->queryOne($sql))
			{
				return $sql;
			}
		}

		$sql = strlen($login) + 2;
		$sql = "SELECT login
				FROM contact_contact
				WHERE login LIKE " . DB()->quote($login . '%') . "
					AND contact_id!={$this->contact_id}
				ORDER BY SUBSTRING(login,{$sql})+0 DESC
				LIMIT 1";

		if ($sql = DB()->queryOne($sql))
		{
			$login .= '.' . (substr($sql, strlen($login) + 1) + 1);
		}

		return $login;
	}
}
