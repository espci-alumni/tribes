<?php

class
{
	const

	PENDING_PERIOD = '4 HOUR',
	MAX_DOUBLON_DISTANCE = 0.2;

	static function getAdminEmails()
	{
		return array('iekhad@hotmail.fr');
	}

	static function getConnectedId($login = true)
	{
		return false;
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
				FROM contact
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

		$sql = "SELECT contact_id, nom_civil, prenom_civil, nom_etudiant, nom_usuel
				FROM contact
				WHERE contact_id!={$contact_id}";

		$data = $data->nom_civil;

		$result = DB()->query($sql);
		while ($row = $result->fetchRow())
		{
			$nom_civil = self::filterLogin($data);

			$d_civil    = self::getLoginDistance($nom_civil, $row->nom_civil);
			$d_usuel    = self::getLoginDistance($nom_civil, $row->nom_usuel);
			$d_etudiant = self::getLoginDistance($nom_civil, $row->nom_etudiant);

			$d_min = min($d_civil, $d_etudiant, $d_usuel);

			if ($d_min <= self::MAX_DOUBLON_DISTANCE)
			{
				$doublons[$row->contact_id . ' '] = $row->prenom_civil . ' ' . $row->nom_civil;
				$distances[] = $d_min;
			}
		}

		array_multisort($distances, $doublons);

		return $doublons;
	}

	static function getLoginDistance($a, $b)
	{
		$b = self::filterLogin($b);

		return levenshtein($a,$b) / max(strlen($a), strlen($b));
	}
}
