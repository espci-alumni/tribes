<?php

class extends loop_edit
{
	protected

	$type = 'email',
	$exposeLoopData = true;


	function __construct($f, $contact_id)
	{
		$loop = new loop_user_email($contact_id);

		parent::__construct($f, $loop);
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);
		$f->add('email', 'email');
	}
}
