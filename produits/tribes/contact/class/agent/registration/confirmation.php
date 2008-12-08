<?php

class extends agent_registration_receipt
{
	function control()
	{
		parent::control();

		if (!$this->data->statut_inscription)
		{
			$sql = "UPDATE contact_email SET contact_confirmed=NOW()
					WHERE token='{$this->data->password_token}'";
			DB()->exec($sql);

			$sql = "UPDATE contact_contact SET statut_inscription='demande'
					WHERE password_token='{$this->data->password_token}'";
			DB()->exec($sql);

			notification::send('registration/request', $this->data);
		}
	}

	protected function save($data)
	{
		$data = parent::save($data);
		$data && $data = 'registration/confirmation/saved';
		return $data;
	}
}
