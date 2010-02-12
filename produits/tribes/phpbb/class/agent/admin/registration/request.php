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
		$is_admin = tribes::isAuth('admin', $contact->contact_id);

		$data = array(
			'username'             => $contact->login,
			'username_clean'       => $contact->user,
			'user_email'           => $contact->email,
			'user_email_hash'      => crc32(strtolower($contact->email)) . strlen($contact->email),
			'user_regdate'         => $_SERVER['REQUEST_TIME'],
			'user_type'            => $is_admin ? 3 : 0, //0 : normal, 1 : deactivated/inactive, 2 : anomyous/bots, 3 : founder
			'group_id'             => $is_admin ? 5 : 2, // REGISTRED
		);

		$db->autoExecute($phpbbDb . '.users', $data);
		$user_id = $db->lastInsertId();

		$sql = "INSERT IGNORE INTO {$phpbbDb}.user_group (user_id,group_id,user_pending)
				VALUES ({$user_id},2,0)";

		if ($is_admin)
		{
			$sql .= ",({$user_id},4,0),({$user_id},5,0)";
		}

		$db->exec($sql);

		return $user_id;
	}
}
