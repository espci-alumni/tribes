<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1' => 0);

	protected

	$requiredAuth = 'admin',
	$confirmed = true;


	function control()
	{
		$this->contact_id = $this->get->__1__;

		parent::control();
	}

	protected function composeForm($o, $f, $send)
	{
		if (!empty($this->data->login))
		{
			$o = $this->composeLogin($o, $f, $send);
			$o = $this->composeNewPassword($o, $f, $send);
		}

		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);

		$this->emails   ->adminMode = true;
		$this->adresses ->adminMode = true;
		$this->activites->adminMode = true;

		return $o;
	}
}
