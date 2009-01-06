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
		'tel_fax'
	);

	function save($data, $message = null, $id = 0)
	{
		$message = parent::save($data, $message, $id);

		if ($this->confirmed)
		{
			if (isset($data['ville']) && $city_id = (int) $data['ville'])
			{
				$sql = "SELECT 1 FROM city WHERE city_id={$city_id}";

				if (!DB()->queryOne($sql))
				{
					$sql = geodb::getCityInfo($city_id);
					unset($sql['region_id'], $sql['search'], $sql['city']);
					DB()->autoExecute('city', $sql);
				}
			}
		}

		return $message;
	}

	protected function filterData($data)
	{
		$adresse = parent::filterData($data);

		if (isset($adresse['ville']))
		{
			$sql = explode(':', $adresse['ville']);
			$adresse['city_id'] = $sql[0];

			$sql = preg_replace('/,.*,/', ',', $sql[1]);
			$sql = explode(',', $sql);
			$adresse['ville'] = $sql[0];
			$adresse['ville'] && $adresse['pays']  = $sql[1];
		}

		return $adresse;
	}
}
