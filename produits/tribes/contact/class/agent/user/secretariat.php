<?php

class extends agent
{
	public $get = array('contact_id:i:1' => 0);

	protected $contact_id = 0;

	function control()
	{
		parent::control();

		$this->contact_id = $this->connected_is_admin && $this->get->contact_id ? $this->get->contact_id : $this->connected_id;
	}
}
