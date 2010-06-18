<?php

class extends self
{
	protected function createAccount($contact)
	{
		$CONFIG['tribes.mediaWikiDb'] && self::mediaWikiCreateAccount($contact);

		return parent::createAccount($contact);
	}

	static function mediaWikiCreateAccount($contact, $user_token = '')
	{
		$db = DB();
		$mediaWikiDb = $CONFIG['tribes.mediaWikiDb'];

		$data = array(
			'user_name'      => ucfirst($contact->user),
			'user_real_name' => $contact->login,
			'user_email'     => $contact->email,
			'user_token'     => $user_token,
			'user_email_authenticated' => date('YmdHis'),
		);

		$db->autoExecute($mediaWikiDb . '.user', $data);
		$user_id = $db->lastInsertId();

		$sql = "INSERT IGNORE INTO {$mediaWikiDb}.user_groups (ug_user,ug_group)
				VALUES ({$user_id},'user')";
		$db->exec($sql);

		return $user_id;
	}
}
