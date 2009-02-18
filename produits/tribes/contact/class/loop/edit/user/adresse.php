<?php

class extends loop_edit
{
	protected

	$type = 'adresse',
	$exposeLoopData = true;

	function __construct($f, $contact_id)
	{
		$loop = new loop_user_adresse($contact_id);

		parent::__construct($f, $loop);
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);

		$f->add('QSelect', 'description', array(
			'src' => 'QSelect/description/adresse',
		));
		$f->add('textarea', 'adresse');
		$f->add('text', 'ville_avant');
		$f->add('city', 'ville');
		$f->add('text', 'ville_apres');
		$f->add('text', 'pays');
		$f->add('textarea', 'email_list');
		$f->add('text', 'tel_portable');
		$f->add('text', 'tel_fixe');
		$f->add('text', 'tel_fax');
		$f->add('check', 'is_shared', array(
			'item' => array (1 => 'Partager'),
			'multiple' => true,
			'isdata' => true,
		));
	}
}
