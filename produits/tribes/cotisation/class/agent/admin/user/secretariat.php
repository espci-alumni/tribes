<?php

class extends self
{
	function composeCotisation($o, $f, $send)
	{
		$f->add('date', 'cotisation_date');
		$f->add(
			'QSelect',
			'cotisation_type',
			array(
				'src' => 'QSelect/suggestions/cotisation/type'
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

		if (!empty($data['cotisation_type']))
		{
			$sql = $db->quote($data['cotisation_type']);
			$sql = "INSERT INTO item_suggestions VALUES ('cotisation/type',{$sql})
					ON DUPLICATE KEY UPDATE suggestion=VALUES(suggestion)";

			$db->exec($sql);
		}

		if (!empty($data['cotisation_date']))
		{
			$d = array(
				'cotisation_date' => $data['cotisation_date'],
				'cotisation_type' => $data['cotisation_type'],
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
