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

	protected function filterData($data)
	{
		$adresse = parent::filterData($data);

		if (isset($adresse['ville']))
		{
			$sql = explode(':', $adresse['ville']);
			$adresse['ville_id'] = $sql[0];

			$sql = preg_replace('/,.*,/', ',', $sql[1]);
			$sql = explode(',', $sql);
			$adresse['ville'] = $sql[0];
			$adresse['ville'] && $adresse['pays']  = $sql[1];
		}

		return $adresse;
	}
}
