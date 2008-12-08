<?php

class extends tribes_contact
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
		'tel_portable',
		'tel_fixe',
		'tel_fax'
	);

	protected function extractData($data)
	{
		$adresse = parent::extractData($data);

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
