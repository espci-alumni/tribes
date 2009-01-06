<?php

class extends self
{
	public $get = array('adresse:i:1', 'contact:i:1');

	function control()
	{
		if (!empty($this->get->contact))
		{
			$this->contact_id = $this->get->contact;
			$this->confirmed = true;
		}

		parent::control();
	}
}
