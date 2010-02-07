<?php

class extends loop_edit
{
	public $adminMode = false;

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
		if (isset($data->contact_confirmed) && !$data->contact_confirmed) unset($data->is_shared);

		$f = $this->form;
		$f->setDefaults($data);

		$s = $f->add('QSelect', 'description', array(
			'isdata' => false,
			'src' => 'QSelect/description/adresse',
			'disabled' => isset($data->c_description) ? !$data->c_description : !empty($data->activite_id),
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
			'description', $this->adminMode ? '' : "Veuillez indiquer la description de votre adresse", '',
			'ville',       $this->adminMode ? '' : "Veuillez choisir ou ajouter une ville", '',
			'is_shared',   $this->adminMode ? '' : "Veuillez choisir le niveau de confidentialité de cette adresse", ''
		);

		$s->attach(
			'adresse',      '', '',
			'ville_avant',  '', '',
			'ville_apres',  '', '',
			'pays',         '', '',
			'email_list',   '', '',
			'tel_portable', '', '',
			'tel_fixe',     '', '',
			'tel_fax',      '', '',
			'is_shared',    '', ''
		);
	}
}
