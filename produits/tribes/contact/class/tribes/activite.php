<?php

class extends tribes_adresse
{
	protected

	$table = 'activite',
	$dataFields = array(
		'service',
		'fonction',
		'secteur',
		'date_debut',
		'date_fin',
		'site_web',
		'keyword',
		'adresse_id',
	);


	function __construct($contact_id, $confirmed = 0)
	{
		parent::__construct($contact_id, $confirmed);

		unset($this->metaFields['is_active']);
	}

	function save($data, $message = null, &$id = 0)
	{
		$message = parent::save($data, $message, $id);

		$this->confirmed = (int) $this->confirmed;

		if (!empty($data['organisation']))
		{
			$db = DB();

			$org = explode('/', $data['organisation']);
			$org = array_map('trim', $org);
			$org = array_unique($org);
			$org = array_map(array($db, 'quote'), $org);

			$o = array();
			$a = array();

			$sql = "DELETE FROM contact_affiliation
					WHERE activite_id={$id}
						AND is_admin_confirmed={$this->confirmed}";
			$db->exec($sql);

			$counter = 0;

			foreach ($org as $org)
			{
				$sql = "SELECT organisation_id, is_obsolete
						FROM contact_organisation
						WHERE organisation={$org}";

				if ($org_id = $db->queryRow($sql))
				{
					if ($org_id->is_obsolete > 0)
					{
						$o[] = $org_id->organisation_id;
					}

					$org_id = $org_id->organisation_id;
				}
				else
				{
					$sql = "INSERT INTO contact_organisation (organisation) VALUES ({$org})";
					$db->exec($sql);
					$org_id = $db->lastInsertId();
				}

				if (!isset($a[$org_id]))
				{
					++$counter;

					$a[$org_id] = "{$id},{$org_id},{$this->confirmed},{$counter}";
				}
			}

			if ($o)
			{
				$sql = implode(',', $o);
				$sql = "UPDATE contact_organisation
						SET is_obsolete=0
						WHERE organisation_id IN ({$sql})";
				$db->exec($sql);
			}

			$sql = implode('),(', $a);
			$sql = "INSERT INTO contact_affiliation VALUES ({$sql})";

			$db->exec($sql);
		}

		return $message;
	}

	protected function filterData($data)
	{
		return tribes_common::filterData($data);
	}
}
