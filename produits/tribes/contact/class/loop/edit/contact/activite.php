<?php

class extends loop_edit
{
	protected

	$type = 'activite',
	$exposeLoopData = true,
	$adresses  = array(),
	$activites = array(),
	$send,
	$editAdresse = true;


	function __construct($f, $contact_id, $send)
	{
		$loop = new loop_contact_activite($contact_id);

		parent::__construct($f, $loop);

		$this->loadAdresses($contact_id);

		$this->send = $send;
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);

		$sql = "SELECT `value` AS K, `group` AS G, `value` AS V
				FROM item_lists
				WHERE type='%s'
				ORDER BY sort_key, `group`, `value`";

		$organisation = $f->add('QSelect', 'organisation', array(
			'isdata' => false,
			'src' => 'QSelect/organisation',
		));
		$f->add('text', 'service');
		$f->add('QSelect', 'titre', array(
			'src' => 'QSelect/activite/titre',
		));
		$f->add('select', 'fonction', array(
			'firstItem' => '- Choisir une fonction -',
			'sql'       => sprintf($sql, 'fonction'),
		));
		$f->add('select', 'secteur', array(
			'firstItem' => '- Choisir un secteur -',
			'sql'       => sprintf($sql, 'secteur'),
		));
		$f->add('monthyear', 'date_debut');
		$f->add('monthyear', 'date_fin');

		if ($this->editAdresse)
		{
			$a = $this->activites
				? array("Coordonnées ci-dessus" => $this->activites)
				: array();

			$a["Coordonnées existantes"] =& $this->adresses;
			$a["Nouvelles coordonnées" ] = array('new' => 'Nouvelles coordonnées');

			$organisation = $organisation->getValue();

			$this->activites[-$counter] = "Idem \"{$organisation}\" ci-dessus";

			$f->add('select', 'adresse_id', array(
				'firstItem' => '- Préciser vos coordonnées -',
				'item' => $a
			));
		}

		$f->add('text', 'site_web');
		$f->add('QSelect', 'keyword', array(
			'src' => 'QSelect/keyword',
		));
		$f->add('check', 'is_shared', array('item' => array(1 => 'Partagé', 0 => 'Confidentiel')));

		$this->send->attach(
			'organisation', "Veuillez renseigner le ou les organisations", '',
			'is_shared', "Veuillez choisir le niveau de partage de cette activité", ''
		);
	}

	protected function loadAdresses($contact_id)
	{
		$a = new loop_contact_adresse($contact_id);

		while ($b = $a->loop())
		{
			$this->adresses[$b->adresse_id] = $b->adresse . ('' !== $b->adresse ? ', ' : '') . $b->ville . '...';
		}
	}
}
