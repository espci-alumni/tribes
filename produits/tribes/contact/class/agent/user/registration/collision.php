<?php

class extends agent_pForm
{
	function control()
	{
		$this->data = s::get('password_contact_id');
		$this->data || p::forbidden();
	}

	protected function save($data)
	{
		s::free('password_contact_id');

		$db = DB();
		$password_token = p::strongid(8);

		$sql = "UPDATE contact
				SET password_token='{$password_token}',
					password_token_expires=NOW() + INTERVAL " . tribes::PENDING_PERIOD . "
				WHERE contact_id={$this->data}";
		$db->exec($sql);

		$sql = "SELECT email
				FROM contact_email
				WHERE contact_id={$this->data}
					AND is_active=1
					AND admin_confirmed
					AND is_obsolete<1";

		notification::send('user/password', array(
			'contact_id' => $this->data,
			'email.To' => $db->queryCol($sql),
			'password_token' => $password_token,
		));

		return 'user/registration/collision/sent';
	}
}
