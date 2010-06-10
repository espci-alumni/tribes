<?php

class extends agent_confirm_email
{
	const NOTIFICATION_DELAY = 300;

	function control()
	{
		$sql = "SELECT contact_id, nom_civil, prenom_civil, sexe
				FROM contact_contact c
					JOIN contact_email e USING (contact_id)
				WHERE e.token='confirm/registration/{$this->get->__1__}'
					AND e.token_expires>NOW()";
		$data = DB()->queryRow($sql);
		$data || p::redirect('error/token');

		tribes_email::confirm("confirm/registration/{$this->get->__1__}");

		pTask::schedule(
			new pTask(
				array('notification', 'send'),
				array('registration/request', $data)
			),
			self::NOTIFICATION_DELAY
		);
	}
}
