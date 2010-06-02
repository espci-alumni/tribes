<?php

class
{
	const

	PENDING_PERIOD = '4 HOUR',
	MAX_DOUBLON_DISTANCE = 0.5;


	static function getConnectedId()
	{
		return (int) s::get('contact_id');
	}

	static function connectedIsAuth($type)
	{
		$id = self::getConnectedId();
		if (!$id)           return false;
		if (-1 === $id)     return true;
		if (true === $type) return true;

		if ($type===s::get('acces')) return true;

		return false;
	}

	static function startFakeSession()
	{
		if (!self::getConnectedId())
		{
			s::set('contact_id', -1);

			SESSION::regenerateId(false, false);
		}
	}

	static function makeIdentifier($a, $auth_chars = 'a-z')
	{
		$a = p::toASCII($a);
		$a = strtolower($a);
		$a = preg_replace("/[^{$auth_chars}]+/", '', $a);

		return $a;
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

		return array_slice($doublons, 0, 10);
	}

	static function getDoublonDistance($a, $b)
	{
		return levenshtein($a, $b) / max(strlen($a), strlen($b));
	}

	protected static $sqlSelectDoublonReference = 'nom_civil, prenom_civil';

	protected static function buildDoublonReference($data)
	{
		return self::makeIdentifier($data->nom_civil) . '.' . self::makeIdentifier($data->prenom_civil);
	}

	protected static function buildDoublonLabel($data)
	{
		return $data->nom_civil . ' ' . $data->prenom_civil;
	}
}
