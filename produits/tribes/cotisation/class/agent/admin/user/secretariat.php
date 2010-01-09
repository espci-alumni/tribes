<?php

class extends self
{
	function composeCotisation($o, $f, $send)
	{
		$f->add('date', 'cotisation_date');
		$f->add('text', 'cotisation_type');

		$send->attach(
			'cotisation_date', 'Merci de saisir une date de cotisation', '',
			'cotisation_type', 'Merci de préciser le type de cotisation', ''
		);

		return $o;
	}

	function save($data)
	{
		if (!empty($data['cotisation_date']))
		{
			$d = array(
				'cotisation_date' => $data['cotisation_date'],
				'cotisation_type' => $data['cotisation_type'],
			);

			unset($data['cotisation_date'], $data['cotisation_type']);

			DB()->autoExecute(
				'contact_contact',
				$d,
				MDB2_AUTOQUERY_UPDATE,
				"contact_id={$this->contact_id}"
			);

			notification::send('user/cotisation', $d + array('contact_id' => $this->contact_id));
		}

		return parent::save($data);
	}
}
