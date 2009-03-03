<?php

class extends loop_edit
{
	protected

	$type = 'adresse',
	$exposeLoopData = true,
	$send;


	function __construct($f, $contact_id, $send)
	{
		$loop = new loop_contact_adresse($contact_id);

		parent::__construct($f, $loop);

		$this->send = $send;
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);

		$f->add('QSelect', 'description', array(
			'isdata' => false,
			'src' => 'QSelect/description/adresse',
		));
		$f->add('textarea', 'adresse');
		$f->add('text', 'ville_avant');
		$f->add('city', 'ville', array('isdata' => false));
		$f->add('text', 'ville_apres');
		$f->add('text', 'pays');
		$f->add('textarea', 'email_list');
		$f->add('text', 'tel_portable');
		$f->add('text', 'tel_fixe');
		$f->add('text', 'tel_fax');
		$f->add('check', 'is_shared', array('item' => array (1 => 'Partagé', 0 => 'Confidentiel')));

		$this->send->attach(
			'description', "Veuillez indiquer la description de votre adresse", '',
			'ville', "Veuillez choisir ou ajouter une ville", '',
			'is_shared', "Veuillez choisir le niveau de confidentialité de cette adresse", ''
		);
	}
}
