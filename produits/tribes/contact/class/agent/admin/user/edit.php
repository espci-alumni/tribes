<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1' => 0);

	function control()
	{
		$this->contact_id = $this->get->__1__;

		parent::control();
	}
}
