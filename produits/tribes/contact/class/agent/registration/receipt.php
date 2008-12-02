<?php

class extends agent_user_edit
{
	function control()
	{
		$token = s::get('registration_token');
		$token || p::forbidden();

		$sql = "SELECT c.contact_id,
					statut_inscription,
					c.contact_confirmed_data,
					password_token,
					email
				FROM contact c JOIN contact_email e ON password_token=token
				WHERE password_token='{$token}'
					AND password_token_expires > NOW()
					AND statut_inscription != 'accepted'";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('error/token');

		$this->data = (object) ((array) $this->data + unserialize($this->data->contact_confirmed_data));
	}
}
