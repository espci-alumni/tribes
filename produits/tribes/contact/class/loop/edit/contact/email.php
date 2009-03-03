<?php

class extends loop_edit
{
	protected

	$type = 'email',
	$exposeLoopData = true,
	$send;


	function __construct($f, $contact_id, $send)
	{
		$loop = new loop_contact_email($contact_id);

		parent::__construct($f, $loop);

		$this->send = $send;
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);
		$f->add('email', 'email', array('readonly' => $data->id, 'isdata' => false));

		$this->send->attach('email', '', "Email non valide");
	}
}
