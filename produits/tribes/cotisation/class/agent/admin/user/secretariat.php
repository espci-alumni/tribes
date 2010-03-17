<?php

class extends self
{
	function composeCotisation($o, $f, $send)
	{
		$f->add('date', 'cotisation_date');
		$f->add(
			'select',
			'cotisation_type',
			array(
				'item' => tribes::getCotisationType(),
			)
		);

		$send->attach(
			'cotisation_date', '', '',
			'cotisation_type', '', ''
		);

		return parent::composeCotisation($o, $f, $send);
	}

	function save($data)
	{
		$db = DB();

		if (!empty($data['cotisation_date']))
		{
			$d = explode('-', $data['cotisation_type'], 2);
			$d = array(
				'cotisation_date' => $data['cotisation_date'],
				'cotisation_type' => $d[1],
			);

			unset($data['cotisation_date'], $data['cotisation_type']);

			$db->autoExecute(
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
