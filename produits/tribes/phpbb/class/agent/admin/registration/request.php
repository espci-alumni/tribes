<?php

class extends self
{
	protected function createAccount($contact)
	{
		$CONFIG['tribes.phpbbDb'] && self::phpbbCreateAccount($contact);

		return parent::createAccount($contact);
	}

	static function phpbbCreateAccount($contact, $password_hash = '')
	{
		$db = DB();
		$phpbbDb = $CONFIG['tribes.phpbbDb'];
		$is_admin = tribes::isAuth('admin', $contact->contact_id);

		$data = array(
			'user_id'              => $contact->contact_id + $CONFIG['tribes.phpbbOffset'],
			'user_password'        => $password_hash,
			'user_pass_convert'    => 0,
			'username'             => $contact->login,
			'username_clean'       => $contact->login,
			'user_email'           => $contact->email,
			'user_email_hash'      => crc32(strtolower($contact->email)) . strlen($contact->email),
			'user_regdate'         => $_SERVER['REQUEST_TIME'],
			'user_type'            => $is_admin ? 3 : 0, //0 : normal, 1 : deactivated/inactive, 2 : anomyous/bots, 3 : founder
			'group_id'             => $is_admin ? 5 : 2, // REGISTRED
			'user_inactive_reason' => 0,
			'user_inactive_time'   => 0,
		);

		$db->autoExecute($phpbbDb . '.users', $data);

		$sql = "INSERT INTO {$phpbbDb}.user_group (user_id,group_id,user_pending)
			VALUES ({$data['user_id']},2,0)";

		if ($is_admin)
		{
			$sql .= ",({$data['user_id']},4,0),({$data['user_id']},5,0)";
		}
		$db->exec($sql);
	}
}
