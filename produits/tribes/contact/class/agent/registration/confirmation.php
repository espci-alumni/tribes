<?php

class extends agent_registration_receipt
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		s::set('registration_token', $this->get->__1__);

		parent::control();

		if (!$this->data->statut_inscription)
		{
			$sql = "UPDATE contact_email SET contact_confirmed=NOW()
					WHERE token='{$this->data->password_token}'";
			DB()->exec($sql);

			$sql = "UPDATE contact SET statut_inscription='demande'
					WHERE password_token='{$this->data->password_token}'";
			DB()->exec($sql);

			notification::send('registration/request', $this->data);
		}
	}

	protected function save($data)
	{
		return 'registration/confirmation/receipt';
	}
}
