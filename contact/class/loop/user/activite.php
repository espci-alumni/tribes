<?php

class extends loop_sql
{
	function __construct($contact_id)
	{
		$sql = "SELECT GROUP_CONCAT(
					organisation
					ORDER BY af.sort_key
					SEPARATOR ' / '
				)
				FROM contact_organisation o
					JOIN contact_affiliation af
						ON af.organisation_id=o.organisation_id
							AND af.is_admin_confirmed
				WHERE af.activite_id=ac.activite_id
				GROUP BY ''";

		$sql = "SELECT activite_id,
					({$sql}) AS organisation,
					service,
					titre,
					fonction,
					secteur,
					IF(date_debut,date_debut,'') AS date_debut,
					IF(date_fin,date_fin,'') AS date_fin,
					site_web,
					keyword,
					ad.adresse_id,
					adresse,
					ville_avant,
					ville,
					ville_apres,
					pays,
					tel_portable,
					tel_fixe,
					tel_fax
				FROM contact_activite ac
					LEFT JOIN contact_adresse ad
						ON ad.adresse_id=ac.adresse_id AND ad.is_shared
				WHERE ac.contact_id={$contact_id}
					AND ac.admin_confirmed
					AND ac.contact_confirmed
					AND ac.is_shared
					AND ac.is_obsolete<=0
				ORDER BY
					IF(ac.date_fin, ac.date_debut, '9999-12-31') DESC,
					IF(ac.date_fin, ac.date_fin,  ac.date_debut) DESC,
					ac.activite_id DESC";

		parent::__construct($sql);
	}
}
