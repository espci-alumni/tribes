<?php

class extends tribes_common
{
	protected

	$table = 'adresse',
	$dataFields = array(
		'adresse',
		'description',
		'ville_avant',
		'ville',
		'ville_apres',
		'pays',
		'email_list',
		'tel_portable',
		'tel_fixe',
		'tel_fax',
	);


	function __construct($contact_id, $confirmed = 0)
	{
		parent::__construct($contact_id, $confirmed);

		$this->metaFields['is_shared'] = 'int';
	}

	function save($data, $message = null, &$id = 0)
	{
		$message = parent::save($data, $message, $id);

		if (!$this->confirmed && $message !== self::ACTION_CONFIRM)
		{
			$this->updateContactModified($id);
		}

		return $message;
	}

	protected function filterData($data)
	{
		$data = parent::filterData($data);

		if (isset($data['ville']) && isset($data['pays']))
		{
			$data['city_id'] = geodb::getCityId($data['ville'] . ', ' . $data['pays']);
	
			if ($data['city_id'] && $this->confirmed)
			{
				$sql = "SELECT 1 FROM city WHERE city_id={$data['city_id']}";

				if (!DB()->queryOne($sql))
				{
					$sql = geodb::getCityInfo($data['city_id']);
					unset($sql['city']);
					DB()->autoExecute('city', $sql);
				}
			}
		}

		return $data;
	}


	function updateContactModified($id)
	{
		$sql = "UPDATE contact_{$this->table}
				SET contact_modified=NOW()
				WHERE contact_id={$this->contact_id}
					AND adresse_id={$id}";
		DB()->exec($sql);

		parent::updateContactModified($this->contact_id);
	}
}
