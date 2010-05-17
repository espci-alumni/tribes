<?php

class extends self
{
	protected static $paiement_mode = array(
		'ESP' => 'Espèces',
		'CHQ' => 'Chèque',
		'VIR' => 'Virement',
	);

	function composeCotisation($o, $f, $send)
	{
		// TODO : ajouter un champ pour saisir l'email
		$f->add('date',  'cotisation_date');
		$f->add('check', 'type', array(	'item' => tribes::getCotisationType()));
		$f->add('text',  'paiement_euro', '\d+([.,]\d*)?');
		$f->add('date',  'paiement_date');
		$f->add('check', 'paiement_mode', array('item' => self::$paiement_mode));
		$f->add('text',  'paiement_ref');

		$send->attach(
			'cotisation_date', '', '',
			'type',            '', '',
			'paiement_euro',   '', 'Merci de saisir un nombre entier ou décimal',
			'paiement_date',   '', '',
			'paiement_mode',   '', '',
			'paiement_ref',    '', ''
		);

		return parent::composeCotisation($o, $f, $send);
	}

	function save($data)
	{
		$url = parent::save($data);

		$db = DB();

		if (isset($data['paiement_euro']) && '' !== $data['paiement_euro'])
		{
			$data = array(
				'cotisation_date' => $data['cotisation_date'],
				'type'            => $data['type'],
				'paiement_euro'   => $data['paiement_euro'],
				'paiement_date'   => $data['paiement_date'],
				'paiement_mode'   => $data['paiement_mode'],
				'paiement_ref'    => $data['paiement_ref'],
			);

			$data['paiement_euro'] = strtr($data['paiement_euro'], ',', '.');
			$data['cotisation_date'] || $data['cotisation_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
			$data['paiement_date']   || $data['paiement_date']   = $data['cotisation_date'];

			list($data['cotisation'], $data['type']) = explode('-', $data['type'], 2);

			$db->autoExecute(
				'contact_contact',
				array(
					'cotisation_date' => $data['cotisation_date'],
				),
				MDB2_AUTOQUERY_UPDATE,
				"contact_id={$this->contact_id}"
			);

			$data += array(
				'token'      => p::strongId(8),
				'soutien'    => $data['paiement_euro'] - $data['cotisation'],
				'contact_id' => $this->contact_id,
			);

			$db->autoExecute('cotisation', $data);

			notification::send('user/cotisation', $data);
		}

		return $url;
	}
}
