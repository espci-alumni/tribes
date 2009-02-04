<?php

class extends self
{
	protected function createAccount($contact)
	{
		$CONFIG['tribes.mediaWikiDb'] && self::mediaWikiCreateAccount($contact);

		return parent::createAccount($contact);
	}

	protected static function mediaWikiCreateAccount($contact)
	{
		$db = DB();

		$data = array(
			'user_id' => $contact->contact_id,
			'user_name' => $contact->login,
			'user_real_name' => $contact->prenom_usuel . ' ' . $contact->nom_usuel,
			'user_email' => $contact->email,
			'user_password' => md5($contact->contact_id . '-' . md5(p::strongid())),
			'user_token' => p::strongid(),
			'user_email_authenticated' => date('YmdHis'),
		);

		$db->autoExecute($CONFIG['tribes.mediaWikiDb'] . '.user', $data);
		$db->autoExecute($CONFIG['tribes.mediaWikiDb'] . '.user_groups', array('ug_user' => $contact->contact_id, 'ug_group' => 'bureaucrat'));
	}
}
