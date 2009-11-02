<?php

class extends self
{
	protected function login($contact)
	{
		$CONFIG['tribes.mediaWikiDb'] && self::mediaWikiLogin($contact);

		return parent::login($contact);
	}

	protected static function mediaWikiLogin($contact)
	{
		$db = DB();
		$mediaWikiDb = $CONFIG['tribes.mediaWikiDb'];

		$sql = "SELECT user_token
			FROM {$mediaWikiDb}.user
			WHERE user_id={$contact->contact_id}";
		$user_token = $db->queryOne($sql);

		if (!$user_token)
		{
			$data = agent_admin_registration_request::mediaWikiCreateAccount($contact);
			$user_token = $data['user_token'];
		}
		else
		{
			$data = array(
				'user_name' => ucfirst($contact->login),
				'user_real_name' => $contact->prenom_usuel . ' ' . $contact->nom_usuel,
				'user_password' => md5($contact->contact_id . '-' . md5($contact->password)),
				'user_email' => $contact->email,
			);

			$db->autoExecute($mediaWikiDb . '.user', $data, MDB2_AUTOQUERY_UPDATE, "user_id={$contact->contact_id}");
		}

		setcookie($mediaWikiDb . 'UserID'  , $contact->contact_id, 0, '/wiki/', $CONFIG['session.cookie_domain']);
		setcookie($mediaWikiDb . 'UserName', $data['user_name']  , 0, '/wiki/', $CONFIG['session.cookie_domain']);
		setcookie($mediaWikiDb . 'Token'   , $user_token         , 0, '/wiki/', $CONFIG['session.cookie_domain']);
	}
}
