<?php

class
{
	const

	PENDING_PERIOD = '4 HOUR',
	MAX_DOUBLON_DISTANCE = 0.5;

	static function getAdminEmails()
	{
		return array('iekhad@hotmail.fr');
	}

	static function getConnectedId($login = true)
	{
		return false;
	}

	static function newInstance($table, $contact_id, $confirmed, $row_id = 0)
	{
		$table = 'tribes_' . $table;
		return new $table($contact_id, $confirmed, $row_id);
	}

	static function filterLogin($a)
	{
		$a = p::toASCII($a);
		$a = strtolower($a);
		$a = preg_replace("/[^a-z]+/", '', $a);

		return $a;
	}

	static function buildLogin($data)
	{
		return self::filterLogin($data['prenom_civil'])
			. '.' . self::filterLogin($data['nom_civil'])
			. '.' . $data['date_naissance'];
	}

	static function getLogin($contact_id, $data)
	{
		$login = self::buildLogin($data);

		$sql = strlen($login) + 2;
		$sql = "SELECT login
				FROM contact_contact
				WHERE login LIKE " . DB()->quote($login . '%') . "
					AND contact_id!={$contact_id}
				ORDER BY SUBSTRING(login,{$sql})+0 DESC
				LIMIT 1";

		if ($sql = DB()->queryOne($sql))
		{
			$login .= '.' . (substr($sql, strlen($login) + 1) + 1);
		}

		return $login;
	}

	static function getDoublonSuggestions($contact_id, $data)
	{
		$doublons  = array();
		$distances = array();

		$data = self::buildDoublonReference($data);

		$sql = "SELECT contact_id, " . self::$sqlSelectDoublonReference . "
				FROM contact_contact
				WHERE contact_id!={$contact_id}";
		$result = DB()->query($sql);
		while ($row = $result->fetchRow())
		{
			$d = self::getDoublonDistance($data, self::buildDoublonReference($row));

			if ($d <= self::MAX_DOUBLON_DISTANCE)
			{
				$doublons[$row->contact_id . ' '] = self::buildDoublonLabel($row);
				$distances[] = $d;
			}
		}

		array_multisort($distances, $doublons);

		return $doublons;
	}

	static function getDoublonDistance($a, $b)
	{
		return levenshtein($a, $b) / max(strlen($a), strlen($b));
	}

	protected static $sqlSelectDoublonReference = 'nom_civil, prenom_civil';

	protected static function buildDoublonReference($data)
	{
		return self::filterLogin($data->nom_civil) . '.' . self::filterLogin($data->prenom_civil);
	}

	protected static function buildDoublonLabel($data)
	{
		return $data->nom_civil . ' ' . $data->prenom_civil;
	}
}
