<?php

class extends loop_user_email
{
	protected

	$form,
	$send,
	$confirmed;

	function __construct($contact_id, $form, $send, $confirmed)
	{
		$this->form = $form;
		$this->send = $send;
		$this->confirmed = $confirmed;

		parent::__construct($contact_id);
	}

	function filterRow($o)
	{
		$o = parent::filterRow($o);

		$this->form->pushContext($o, $this->table . $o->id);

		$status = array(0 => 'À jour', 1 => 'Obsolète');
		$this->confirmed && $status[-1] = 'À vérifier';

		$this->form->add('check', 'is_obsolete', array(
			'item' => $status,
			'default' => $o->is_obsolete,
		));

		$this->send->attach('is_obsolete', 'Quel est le statut de cette adresse ?', '');

		$this->form->pullContext();

		return $o;
	}
}
