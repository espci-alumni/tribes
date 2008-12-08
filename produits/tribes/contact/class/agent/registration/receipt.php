<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[A-Za-z0-9]{8}', 'email:i:1', 'adresse:i:1');

	function control()
	{
		$token = empty($this->get->__1__) ? s::get('registration_token') : $this->get->__1__;
		$token || p::forbidden();

		$sql = "SELECT contact_id, statut_inscription
				FROM contact_contact
				WHERE password_token='{$token}'
					AND password_token_expires > NOW()
					AND statut_inscription != 'accepted'";
		$data = DB()->queryRow($sql);

		$data || p::redirect('error/token');

		$this->get->contact = $data->contact_id;

		parent::control();

		$this->data->password_token = $token;
		$this->data->statut_inscription = $data->statut_inscription;
	}

	protected function save($data)
	{
		empty($this->get->__1__) || s::free('registration_token');

		$data = parent::save($data);
		$data && $data = 'registration/receipt/saved';
		return $data;
	}
}
