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
		'login',
	);

	static

	$alias = array(
		array('prenom_civil', 'nom_usuel'),
		array('prenom_usuel', 'nom_etudiant'),
		array('prenom_civil', 'nom_civil'),
	);


	function __construct($contact_id, $confirmed = false)
	{
		$this->metaFields += array(
			'conjoint_contact_id' => 'stringNull',
			'token'               => 'stringNull',
			'token_expires'       => 'sql',
			'statut_inscription'  => 'string',
			'reference'           => 'string',
			'password'            => 'string',
			'photo_token'         => 'string',
			'cv_token'            => 'string',
			'cv_text'             => 'string',
		);

		parent::__construct($contact_id, $confirmed);

		$contact_id || $this->contact_id = 0;
	}

	function save($data, $message = null, &$id = 0)
	{
		if (isset($data['reference']))
		{
			W(__METHOD__ . '() input error: $data[\'reference\'] must not be set');
			unset($data['reference']);
		}

		if ($this->confirmed || !$this->contact_id)
		{
			if ($data['reference'] = self::buildReference($data))
			{
				$data['reference'] = $this->buildUniqueReference($data['reference']);
			}
			else
			{
				$this->contact_id || W(__METHOD__ . '() error: unable to build a valid reference');

				unset($data['reference']);
			}
		}

		if (!$this->contact_id)
		{
			$data['photo_token'] = p::strongid(8);
			$data['cv_token']    = p::strongid(8);
		}

		if ( !$this->confirmed
			&& !empty($data['login'])
			&& !empty($this->contactData['login'])
			&& $data['login'] !== $this->contactData['login'] )
		{
			$login = str_replace('-', '', $data['login']);

			$db = DB();
			$sql = "SELECT 1
					FROM contact_alias
					WHERE contact_id={$this->contact_id}
						AND alias='{$login}'";

			if ($db->queryOne($sql))
			{
				$this->contactData['login'] = $data['login'];
				$login = $db->quote(serialize($this->contactData));

				$sql = "UPDATE contact_contact
						SET login='{$data['login']}', contact_data={$login}
						WHERE contact_id={$this->contact_id}";
				$db->exec($sql);
			}
		}

		$message = parent::save($data, $message, $this->contact_id);

		if (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message)
		{
			if ($this->confirmed)
			{
				$db = DB();

				if (isset($data['reference']))
				{
					$sql = "INSERT INTO contact_alias (alias,contact_id,hidden)
							VALUES ('{$data['reference']}',{$this->contact_id},1)
							ON DUPLICATE KEY UPDATE contact_id={$this->contact_id}";
					$db->exec($sql);
				}

				if (isset($data['login']))
				{
					$login = str_replace('-', '', $data['login']);

					$sql = "INSERT IGNORE INTO contact_alias (alias,contact_id)
							VALUES ('{$login}',{$this->contact_id})";
					$db->exec($sql);
				}

				for ($i = 0; $i < count(self::$alias); ++$i)
				{
					$sql = self::$alias[$i];

					if (!isset($data[$sql[0]])) continue;
					if (!isset($data[$sql[1]])) continue;

					$login = tribes::makeIdentifier($data[$sql[0]], '-a-z') . '.' . tribes::makeIdentifier($data[$sql[1]], '-a-z');
					$sql = "INSERT IGNORE INTO contact_alias (contact_id, alias)
							VALUES ({$this->contact_id},'" . str_replace('-', '', $login) . "')";

					if ($db->exec($sql))
					{
						$sql = "UPDATE contact_contact
								SET login='{$login}'
								WHERE contact_id={$this->contact_id}
									AND login=''";
						$db->exec($sql);
					}
				}
			}
			else
			{
				$this->updateContactModified($this->contact_id);
			}
		}

		return $message;
	}

	function delete($contact_id)
	{
		$sql = "DELETE FROM contact_alias WHERE contact_id={$contact_id}";
		DB()->exec($sql);

		parent::delete($contact_id);
	}

	protected function filterMeta($data)
	{
		$data = parent::filterMeta($data);

		if (isset($data['conjoint_contact_id']) && 'NULL' !== $data['conjoint_contact_id'])
		{
			$sql = str_replace('-', '', $data['conjoint_contact_id']);
			$sql = "SELECT contact_id
					FROM contact_alias
					WHERE alias={$sql}";

			$data['conjoint_contact_id'] = DB()->queryOne($sql);
			$data['conjoint_contact_id'] || $data['conjoint_contact_id'] = 'NULL';
		}

		return $data;
	}


	static function buildReference($data)
	{
		$reference = false;

		if (   isset($data['prenom_civil'])
			&& isset($data['nom_civil'])
			&& isset($data['date_naissance']))
		{
			$reference = tribes::makeIdentifier($data['prenom_civil'])
				. '.' . tribes::makeIdentifier($data['nom_civil'])
				. '.' . $data['date_naissance'];
		}

		return $reference;
	}


	protected function buildUniqueReference($reference)
	{
		$db = DB();

		if ($this->contact_id)
		{
			$sql = "SELECT reference
					FROM contact_contact
					WHERE reference LIKE " . $db->quote($reference . '%') . "
						AND contact_id={$this->contact_id}";
			if ($sql = $db->queryOne($sql))
			{
				return $sql;
			}
		}

		$sql = strlen($reference) + 2;
		$sql = "SELECT reference
				FROM contact_contact
				WHERE reference LIKE " . $db->quote($reference . '%') . "
					AND contact_id!={$this->contact_id}
				ORDER BY SUBSTRING(reference,{$sql})+0 DESC
				LIMIT 1";

		if ($sql = $db->queryOne($sql))
		{
			$reference .= '.' . (substr($sql, strlen($reference) + 1) + 1);
		}

		return $reference;
	}
}
