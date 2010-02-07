<?php

class extends loop_edit
{
	public $adminMode = false;

	protected

	$type = 'email',
	$exposeLoopData = true,
	$send,
	$contact_id,
	$activableNb;


	function __construct($f, $contact_id, $send)
	{
		$this->contact_id = $contact_id;

		$loop = new loop_contact_email($contact_id);

		$this->defaultLength = s::get('contact_id') == $contact_id ? 1 : 0;

		parent::__construct($f, $loop);

		$this->send = $send;

		// Stub to detect easily when no box has been checked

		$f->add('hidden', 'is_active', array(
			'valid' => 'c', '[0-9]+',
			'multiple' => true,
		));

		$send->attach('is_active', "Merci d'activer au moins une redirection", '');
	}

	protected function prepare()
	{
		if (!$this->adminMode)
		{
			$sql = "SELECT COUNT(*)
					FROM contact_email
					WHERE contact_id={$this->contact_id}
						AND is_obsolete<=0
						AND contact_data!=''
						AND admin_confirmed";
			$this->activableNb = DB()->queryOne($sql);
		}

		return parent::prepare();
	}

	protected function next()
	{
		if (!$this->adminMode && $this->activableNb <= 1)
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
