<?php

class extends tribes_adresse
{
	protected

	$table = 'activite',
	$dataFields = array(
		'service',
		'titre',
		'fonction',
		'secteur',
		'date_debut',
		'date_fin',
		'site_web',
		'keyword',
		'adresse_id',
	);


	function __construct($contact_id, $confirmed = false)
	{
		parent::__construct($contact_id, $confirmed);

		unset($this->metaFields['is_active']);
	}

	function save($data, $message = null, &$id = 0)
	{
		$message = parent::save($data, $message, $id);

		$org_inserted = false;

		if (!empty($data['organisation']))
		{
			$db = DB();

			$confirmed = (int) (bool) $this->confirmed;

			$org = explode('/', $data['organisation']);
			$org = array_map('trim', $org);
			$org = array_unique($org);

			$o = array();
			$a = array();

			$sql = "DELETE FROM contact_affiliation
					WHERE activite_id={$id}
						AND is_admin_confirmed={$confirmed}";
			$db->exec($sql);

			$counter = 0;

			foreach ($org as $org)
			{
				if ('' === $org) continue;

				$q_org = $db->quote($org);

				$sql = "SELECT organisation_id, organisation, is_obsolete
						FROM contact_organisation
						WHERE organisation={$q_org}";

				if ($org_id = $db->queryRow($sql))
				{
					if ($org_id->is_obsolete > 0)
					{
						$o[] = $org_id->organisation_id;
					}

					if ($confirmed && $org !== $org_id->organisation)
					{
						$sql = "UPDATE contact_organisation
								SET organisation={$q_org}
								WHERE organisation_id={$org_id->organisation_id}";
						$db->exec($sql);
					}

					$org_id = $org_id->organisation_id;
				}
				else
				{
					$sql = 1 - $confirmed;
					$sql = "INSERT INTO contact_organisation (organisation, is_obsolete)
							VALUES ({$q_org},{$sql})";
					$db->exec($sql);
					$org_id = $db->lastInsertId();
					$org_inserted = true;
				}

				if (!isset($a[$org_id]))
				{
					++$counter;

					$a[$org_id] = "{$id},{$org_id},{$confirmed},{$counter}";
				}
			}

			if ($o && $this->confirmed)
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

		if (!$this->confirmed && self::ACTION_CONFIRM === $message && $org_inserted)
		{
			$this->updateContactModified($id);
		}

		return $message;
	}

	protected function filterData($data)
	{
		$data = tribes_common::filterData($data);

		isset($data['service'])  && $data['service']  = u::ucfirst($data['service']);
		isset($data['fonction']) && $data['fonction'] = u::ucfirst($data['fonction']);
		isset($data['secteur'])  && $data['secteur']  = u::ucfirst($data['secteur']);

		return $data;
	}
}
