<?php

// TODO : Remplacer contact_contact.cotisation_date par cotisation_expires
// Calculer cotisation_expires en fonction de la date de cotisation, via un tableau :
// type -> interval de prolongation par rapport au MAX(NOW(), cotisation_expires)
//
// TODO : reprendre l'info "promotion" sans créer de dépendence forte, sinon la documenter

class extends self
{
	protected static $cotisation_type;

	static function getCotisationType()
	{
		if (!isset(self::$cotisation_type))
		{
			$sql = "SELECT value FROM item_lists WHERE type='cotisation/type' ORDER BY sort_key";
			$result = DB()->query($sql);

			while ($row = $result->fetchRow())
			{
				$c = explode('-', $row->value, 2);

				self::$cotisation_type[$row->value] = $c[1] . ' - ' . $c[0] . ' €';
			}
		}

		return self::$cotisation_type;
	}
}
