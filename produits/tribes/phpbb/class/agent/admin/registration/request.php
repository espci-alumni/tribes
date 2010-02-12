<?php

class extends self
{
	protected function createAccount($contact)
	{
		$CONFIG['tribes.phpbbDb'] && self::phpbbCreateAccount($contact);

		return parent::createAccount($contact);
	}

	static function phpbbCreateAccount($contact)
	{
		$db = DB();
		$phpbbDb = $CONFIG['tribes.phpbbDb'];

		$data = array(
			'username'             => $contact->login,
			'username_clean'       => $contact->user,
			'user_email'           => $contact->email,
			'user_email_hash'      => crc32(strtolower($contact->email)) . strlen($contact->email),
			'user_regdate'         => $_SERVER['REQUEST_TIME'],
			'group_id'             => 2,
		);

		$db->autoExecute($phpbbDb . '.users', $data);
		$user_id = $db->lastInsertId();

		$sql = "INSERT IGNORE INTO {$phpbbDb}.user_group (user_id,group_id,user_pending)
				VALUES ({$user_id},2,0)";

		if (tribes::isAuth('admin', $contact->contact_id))
		{
			$sql .= ",({$user_id},4,0),({$user_id},5,0)";
		}

		$db->exec($sql);

		return $user_id;
	}
}
