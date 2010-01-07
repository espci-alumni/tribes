<?php

class extends loop_edit
{
	protected

	$type = 'adresse',
	$exposeLoopData = true,
	$send;


	function __construct($f, $contact_id, $send, $new = false)
	{
		$loop = new loop_contact_adresse($contact_id, $new > 0);

		$new && $this->allowAddDel = false;
		$this->defaultLength = s::get('contact_id') == $contact_id ? 1 : 0;

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
			'disabled' => !empty($data->activite_id),
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
