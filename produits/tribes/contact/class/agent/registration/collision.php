<?php

class extends agent_pForm
{
	public $get = '__1__:c:[-_A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$sql = "SELECT contact_id
				FROM contact_email
				WHERE token='{$this->get->__1__}'
					AND token_expires > NOW()";
		$this->data = DB()->queryOne($sql);
		$this->data || p::forbidden();
	}

	protected function save($data)
	{
		$contact = new tribes_contact($this->data);
		$contact->save(
			array('token' => p::strongid(8)),
			'user/password/request'
		);

		return 'registration/collision/sent';
	}
}
