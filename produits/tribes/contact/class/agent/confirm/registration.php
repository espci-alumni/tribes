<?php

class extends agent_confirm_email
{
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

		notification::send('registration/request', $data);
	}
}
