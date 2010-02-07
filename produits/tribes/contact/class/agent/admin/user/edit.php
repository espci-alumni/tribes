<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1' => 0);

	function control()
	{
		$this->contact_id = $this->get->__1__;

		parent::control();
	}

	protected function composeForm($o, $f, $send)
	{
		if ($this->data->login)
		{
			$o = $this->composeLogin($o, $f, $send);
			$o = $this->composeNewPassword($o, $f, $send);
		}

		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);

		$send->attach('is_active', '', '');

		$o->emails   ->adminMode = true;
		$o->adresses ->adminMode = true;
		$o->activites->adminMode = true;

		return $o;
	}

	protected function save($data)
	{
		$this->saveContact($data);
		$this->saveEmail($data);
		$this->saveAdresse($data);
		$new_adresse_url = $this->saveActivite($data);

		return $new_adresse_url;
	}
}
