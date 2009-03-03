<?php

class extends loop_user_adresse
{
	protected

	$table = 'activite',
	$select = '
		(
			SELECT GROUP_CONCAT(
				organisation
				ORDER BY af.sort_key
				SEPARATOR " / "
			)
			FROM contact_organisation o
			JOIN contact_affiliation af
				ON af.organisation_id=o.organisation_id
					AND af.is_admin_confirmed
			WHERE af.activite_id=contact_activite.activite_id
			GROUP BY ""
		) AS organisation,
		service,
		titre,
		fonction,
		secteur,
		IF(date_debut,date_debut,"") AS date_debut,
		IF(date_fin,date_fin,"") AS date_fin,
		site_web';
}
