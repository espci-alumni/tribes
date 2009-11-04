<?php

class extends loop_contact_email
{
	protected

	$table = 'activite',
	$extraSelect = "contact_modified,
					adresse_id,
					is_shared,
					(
						SELECT GROUP_CONCAT(organisation ORDER BY af.sort_key SEPARATOR ' / ')
						FROM contact_organisation o
							JOIN contact_affiliation af
								ON af.organisation_id=o.organisation_id
									AND NOT is_admin_confirmed
						WHERE af.activite_id=contact_activite.activite_id
						GROUP BY ''
					) AS organisation";
}
