<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[A-Za-z0-9]{4}');

	protected

	$requiredAuth = false,
	$loginField = false;

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
	}

	protected function save($data)
	{
		$data = parent::save($data);
		false !== $data && $data = 'registration/receipt/saved';
		return $data;
	}

	protected function saveAdresse($data)
	{
		$data['contact_confirmed'] = true;
		parent::saveAdresse($data);
	}

	protected function composeActivite($o, $f, $send)
	{
		return $o;
	}

	protected function saveActivite($data)
	{
	}
}
