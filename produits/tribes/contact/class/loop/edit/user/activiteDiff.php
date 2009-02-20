<?php

class extends loop_edit_user_activite
{
	protected $orgSeparator;

	function __construct($f, $contact_id)
	{
		$this->orgSeparator = $sql = p::strongid(4);

		$sql = "SELECT activite_id,
					(
						SELECT GROUP_CONCAT(
							CONCAT(is_admin_confirmed, organisation)
							ORDER BY af.sort_key
							SEPARATOR '{$sql}'
						)
						FROM contact_organisation o
							JOIN contact_affiliation af
								ON af.organisation_id=o.organisation_id
						WHERE af.activite_id=contact_activite.activite_id
						GROUP BY ''
					) AS organisation,

					fonction      AS c_fonction,
					service       AS c_service,
					secteur       AS c_secteur,
					date_debut    AS c_date_debut,
					date_fin      AS c_date_fin,
					adresse_id    AS c_adresse_id,
					site_web      AS c_site_web,
					keyword       AS c_keyword,
					admin_confirmed,
					contact_data
				FROM contact_activite
				WHERE contact_id={$contact_id}
					AND admin_confirmed<contact_modified";
		$loop = new loop_sql($sql, array($this, 'filterActivite'));

		loop_edit::__construct($f, $loop);

		$this->loadAdresses($contact_id);
	}

	function filterActivite($o)
	{
		$o = (object) ((array) $o + unserialize($o->contact_data));

		!(int) $o->admin_confirmed && $o->new_activite = 1;

		$a = explode($this->orgSeparator, $o->organisation);

		$org   = array();
		$c_org = array();

		foreach ($a as $a)
		{
			if ('0' === $a[0]) $org[] = substr($a, 1);
			else $c_org[] = substr($a, 1);
		}

		$o->c_organisation = implode(' / ', $c_org);
		$o->organisation   = implode(' / ',   $org);

		return $o;
	}
}
