<?php

class extends agent_user_edit
{
	public $get = array('__1__:c:[-_A-Za-z0-9]{4}');

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
	}

	protected function composeForm($o, $f, $send)
	{
		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);

		return $o;
	}

	protected function save($data)
	{
		$data['contact_confirmed'] = true;

		$this->saveContact($data);
		$this->saveEmail($data);
		$this->saveAdresse($data);

		return 'registration/receipt/saved';
	}
}
