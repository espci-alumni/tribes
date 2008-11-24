<?php

class extends agent_form
{
	protected $maxage = -1;

	protected function composeForm($o, $f, $send)
	{
		$f->add('email', 'email');

		$send->attach('email', 'Veuillez renseigner votre email', '');

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT contact_id FROM contact_email WHERE email=" . $db->quote(strtolower($data['email']));

		if ($sql = $db->queryOne($sql))
		{
			$password_token = p::strongid(8);

			$sql = "UPDATE contact
					SET password_token='{$password_token}',
						password_token_date=NOW()
					WHERE contact_id={$sql}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => $data['email']),
				'email/user/password/recover',
				array('password_token' => $password_token)
			);
	
			return 'index';
		}
		else return 'index';
	}
}
