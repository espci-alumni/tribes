<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[A-Za-z0-9]{4}', 'email:i:1', 'adresse:i:1');

	protected $requiredAuth = false;

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$sql = "SELECT contact_id, statut_inscription
				FROM contact_contact
				WHERE token LIKE '{$this->get->__1__}%'
					AND token_expires > NOW()
					AND statut_inscription != 'accepted'";
		$data = DB()->queryRow($sql);
		$data || p::redirect('error/token');

		$this->contact_id = $data->contact_id;

		parent::control();

		$this->mandatoryAdresse = false;
	}

	protected function save($data)
	{
		$data = parent::save($data);
		false !== $data && $data = 'registration/receipt/saved';
		return $data;
	}

	protected function saveFormAdresse($data)
	{
		$data['contact_confirmed'] = true;
		parent::saveFormAdresse($data);
	}
}
