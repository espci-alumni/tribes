<?php

class extends loop_edit
{
	protected

	$type = 'activite',
	$exposeLoopData = true,
	$adresses = array();


	function __construct($f, $contact_id)
	{
		$loop = new loop_user_activite($contact_id);

		parent::__construct($f, $loop);

		$this->loadAdresses($contact_id);
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);

		$f->add('QSelect', 'organisation', array(
			'src' => 'QSelect/organisation',
		));
		$f->add('QSelect', 'secteur', array(
			'src' => 'QSelect/activite/secteur',
		));
		$f->add('text', 'service');
		$f->add('QSelect', 'fonction', array(
			'src' => 'QSelect/activite/fonction',
		));
		$f->add('date', 'date_debut');
		$f->add('date', 'date_fin');
		$f->add('select', 'adresse_id', array(
			'firstItem' => '',
			'item' => &$this->adresses,
		));
		$f->add('text', 'site_web');
		$f->add('text', 'keyword');
		$f->add('check', 'is_shared', array(
			'item' => array (1 => 'Partager'),
			'multiple' => true,
			'isdata' => true,
		));
	}

	protected function loadAdresses($contact_id)
	{
		$a = new loop_user_adresse($contact_id);

		while ($b = $a->loop())
		{
			$this->adresses[$b->adresse_id] = $b->adresse . ('' !== $b->adresse ? ', ' : '') . $b->ville . '...';
		}
	}
}
