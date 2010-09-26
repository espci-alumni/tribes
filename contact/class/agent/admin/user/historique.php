<?php

class extends agent_user_historique
{
	public $get = array('__1__:i:1');

	protected $requiredAuth = 'admin';


	function compose($o)
	{
		$this->contact_id = $this->get->__1__;

		return parent::compose($o);
	}
}
