<?php

class extends loop_sql
{
	function __construct($contact_id, $new = false)
	{
		$sql = "SELECT GROUP_CONCAT(
					organisation
					ORDER BY af.sort_key
					SEPARATOR ' / '
				)
				FROM contact_organisation o
				JOIN contact_affiliation af
					ON af.organisation_id=o.organisation_id
						AND NOT af.is_admin_confirmed
				WHERE af.activite_id=ac.activite_id
				GROUP BY ''";

		$sql = "SELECT
					ac.*,
					ad.adresse_id,
					ad.adresse_id AS id,
					ad.is_obsolete,
					IF(ad.admin_confirmed,ad.admin_confirmed,'') AS admin_confirmed,
					IF(ad.contact_confirmed,ad.contact_confirmed,'') AS contact_confirmed,
					ad.contact_data,
					IF(ad.contact_modified,ad.contact_modified,'') AS contact_modified, ad.is_active, ad.is_shared,
					IF (NOT ISNULL(ac.activite_id),({$sql}),'') AS organisation
				FROM contact_adresse ad ";

		if ($new)
		{
			$sql .= "JOIN contact_activite ac ON ac.adresse_id=ad.adresse_id
				WHERE ad.contact_id={$contact_id} AND ad.is_obsolete=0 AND ad.contact_data=''
				ORDER BY ac.sort_key";
		}
		else
		{
			$sql .= "LEFT JOIN contact_activite ac ON ac.adresse_id=ad.adresse_id
				WHERE ad.contact_id={$contact_id} AND ad.is_obsolete<=0 AND ad.contact_data!=''
				ORDER BY ad.sort_key";
		}

		parent::__construct($sql, array($this, 'filterRow'));
	}

	function filterRow($o)
	{
		empty($o->contact_data) || $o = (object) ((array) $o + unserialize($o->contact_data));

		unset($o->contact_data);

		return $o;
	}
}
