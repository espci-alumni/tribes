<?php

// TODO ! Remplacer contact_contact.cotisation_date par cotisation_expires
// Calculer cotisation_expires en fonction de la date de cotisation, via un tableau :
// type -> interval de prolongation par rapport au MAX(NOW(), cotisation_expires)
//
// TODO : reprendre l'info "promotion" sans créer de dépendence forte

class extends self
{
	protected static $cotisation_type;

	static function getCotisationType($key = null)
	{
		if (!isset(self::$cotisation_type))
		{
			$sql = "SELECT value FROM item_lists WHERE type='cotisation/type' ORDER BY sort_key";
			$result = DB()->query($sql);

			while ($row = $result->fetchRow())
			{
				$c = explode(':', $row->value, 2);

				self::$cotisation_type[$c[0]] = $c[1];
			}
		}

		return null === $key ? self::$cotisation_type : (isset(self::$cotisation_type[$key]) ? self::$cotisation_type[$key] : $key);
	}
}
