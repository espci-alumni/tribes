<?php

class extends agent_user_edit
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		$password_token = $this->get->__1__;
		$password_token || p::forbidden();

		$db = DB();

		$sql = "SELECT c.contact_id,
					statut_inscription,
					c.contact_confirmed_data,
					password_token,
					date_naissance,
					email
				FROM contact c JOIN contact_email e ON password_token=token 
				WHERE password_token='{$password_token}'
					AND password_token_expires > NOW()
					AND statut_inscription != 'accepted'";
		$this->data = $db->queryRow($sql);
		$this->data || p::redirect('error/token');

		$this->data = (object) ((array) $this->data + unserialize($this->data->contact_confirmed_data));

		if (!$this->data->statut_inscription)
		{
			$sql = "UPDATE contact_email SET contact_confirmed=NOW()
					WHERE token='{$password_token}'";
			$db->exec($sql);

			$sql = "UPDATE contact SET statut_inscription='demande'
					WHERE password_token='{$password_token}'";
			$db->exec($sql);

			notification::send('user/registration/request', $this->data);
		}
	}

	protected function save($data)
	{
		return 'user/registration/confirmation/receipt';
	}
}
