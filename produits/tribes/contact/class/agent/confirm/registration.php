<?php

class extends agent_registration_receipt
{
	const NOTIFICATION_DELAY = 300;

	public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

	function control()
	{
		$token = $this->get->__1__;
		$token || p::forbidden();

		$sql = "UPDATE contact_contact
				SET statut_inscription='demande'
				WHERE token='{$token}'
					AND token_expires > NOW()
					AND statut_inscription=''";
		if (DB()->exec($sql))
		{
			tribes_email::confirm($token, false);

			$notice = true;
		}

		parent::control();

		if (!empty($notice))
		{
			$this->data->token = $token;

			$notice = new pTask(
				array('notification', 'send'),
				array('registration/request', $this->data)
			);

			$notice->run(self::NOTIFICATION_DELAY);
		}
	}

	protected function save($data)
	{
		$data = parent::save($data);
		false !== $data && $data = 'confirm/registration/saved';
		return $data;
	}
}
