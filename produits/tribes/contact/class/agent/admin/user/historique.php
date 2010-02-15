<?php

class extends agent_user_historique
{
	public $get = array('__1__:i:1');

	function compose($o)
	{
		$this->contact_id = $this->get->__1__;
		$o = parent::compose($o);
		$o->contact_id = $this->contact_id;

		return $o;
	}
}
