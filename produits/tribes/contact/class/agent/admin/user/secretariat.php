<?php

class extends agent_pForm
{
	public $get = array('contact_id:i:1');

	protected

	$requiredAuth = 'admin',
	$contact_id,
	$form;


	function compose($o)
	{
		$this->contact_id = $this->get->contact_id;

		$o = substr_replace(get_class($this), '', 6, 6);
		$o = patchwork_class2file(substr($o, 6));
		$o = agent::get($o, (array) $this->get);
		$o->contact_id = $this->contact_id;

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$this->form = $f;

		return parent::composeForm($o, $f, $send);
	}
}
