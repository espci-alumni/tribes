<?php

class extends loop_edit
{
	protected

	$type = 'email',
	$exposeLoopData = true,
	$send,
	$activableNb;


	function __construct($f, $contact_id, $send)
	{
		$loop = new loop_contact_email($contact_id);

		parent::__construct($f, $loop);

		$this->send = $send;

		$sql = "SELECT COUNT(*)
				FROM contact_email
				WHERE contact_id={$contact_id}
					AND is_obsolete<=0
					AND contact_data!=''
					AND admin_confirmed";
		$this->activableNb = DB()->queryOne($sql);


		// Stub to detect easily when no box has been checked

		$f->add('hidden', 'is_active', array(
			'valid' => 'c', '[0-9]+',
			'multiple' => true,
		));

		$send->attach('is_active', "Merci d'activer au moins une redirection", '');
	}

	protected function next()
	{
		if ($this->activableNb <= 1)
		{
			$this->allowAddDel = false;
		}

		if ($a = parent::next())
		{
			if (!empty($a->deleted) && !empty($a->admin_confirmed))
			{
				--$this->activableNb;
			}
		}

		$this->allowAddDel = true;

		return $a;
	}

	function populateForm($a, $data, $counter)
	{
		$f = $this->form;
		$f->setDefaults($data);
		$f->add('email', 'email', array('readonly' => $data->id, 'isdata' => false));

		$this->send->attach('email', '', "Email non valide");

		if (!empty($data->admin_confirmed))
		{
			$f->pullContext();

			$a->f_is_active = new pForm_check(
				$f, 'f_is_active',
				array(
					'item' => array($a->email_id => 'Redirection'),
					'default' => $data->is_active ? $a->email_id : '',
					'multiple' => true
				)
			);

			$f->pushContext($a, $this->type . '_' . $counter);
		}
	}
}
