<?php

class extends self
{
	protected function login($contact)
	{
		parent::login($contact);

		$CONFIG['tribes.phpbbDb'] && self::phpbbLogin($contact);
	}

	protected static function phpbbLogin($contact)
	{
		$user_id = $contact->contact_id + $CONFIG['tribes.phpbbOffset'];

		$db = DB();
		$phpbbDb = $CONFIG['tribes.phpbbDb'];
		$sid = s::getSID();
		$is_admin = tribes::isAuth('admin', $contact->contact_id);

		$data = array(
			'user_password'        => self::hash($contact->password),
			'user_pass_convert'    => 0,
			'username'             => $contact->login,
			'username_clean'       => $contact->login,
			'user_email'           => $contact->email,
			'user_email_hash'      => crc32(strtolower($contact->email)) . strlen($contact->email),
		);

		$sql = $db->autoExecute(
			$phpbbDb . '.users',
			$data,
			MDB2_AUTOQUERY_UPDATE,
			"user_id={$user_id}"
		);

		if ($sql)
		{
			if ($is_admin)
			{
				$sql = "INSERT IGNORE INTO {$phpbbDb}.user_group (user_id,group_id,user_pending)
					VALUES ({$user_id},4,0),({$user_id},5,0)";
			}
			else
			{
				$sql = "DELETE FROM {$phpbbDb}.user_group WHERE user_id={$user_id} AND group_id IN (4,5)";
			}

			$db->exec($sql);
		}
		else
		{
			agent_admin_registration_request::phpbbCreateAccount($contact, self::hash($contact->password));
		}

		$data = array(
			'session_id'         => $sid,
			'session_user_id'    => $user_id,
			'session_last_visit' => $_SERVER['REQUEST_TIME'],
			'session_start'      => $_SERVER['REQUEST_TIME'],
			'session_time'       => $_SERVER['REQUEST_TIME'],
			'session_ip'         => $_SERVER['REMOTE_ADDR'],
			'session_browser'    => $_SERVER['HTTP_USER_AGENT'],
			'session_page'       => 'index.php',
			'session_autologin'  => 1,
			'session_admin'      => $is_admin ? 1 : 0,
		);

		$db->autoExecute($phpbbDb . '.sessions', $data, MDB2_AUTOQUERY_INSERT);

		setcookie($phpbbDb . '_u'  , $user_id, 0, '/forum/', $CONFIG['session.cookie_domain']);
		setcookie($phpbbDb . '_sid', $sid    , 0, '/forum/', $CONFIG['session.cookie_domain']);
	}


	/**
	*
	* @version Version 0.1 / slightly modified for phpBB 3.0.x (using $H$ as hash type identifier)
	*
	* Portable PHP password hashing framework.
	*
	* Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
	* the public domain.
	*
	*/

	/**
	* Hash the password
	*/
	static function hash($password)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$random_state = p::strongID();
		$random = '';
		$count = 6;

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5(p::strongID() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$hash = self::_hash_crypt_private($password, self::_hash_gensalt_private($random, $itoa64), $itoa64);

		if (strlen($hash) == 34)
		{
			return $hash;
		}

		return md5($password);
	}

	/**
	* Generate salt for hash generation
	*/
	static protected function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}

		$output = '$H$';
		$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= self::_hash_encode64($input, 6, $itoa64);

		return $output;
	}

	/**
	* Encode hash
	*/
	static protected function _hash_encode64($input, $count, &$itoa64)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= $itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= $itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}

	/**
	* The crypt function/replacement
	*/
	static protected function _hash_crypt_private($password, $setting, &$itoa64)
	{
		$output = '*';

		// Check for correct hash
		if (substr($setting, 0, 3) != '$H$')
		{
			return $output;
		}

		$count_log2 = strpos($itoa64, $setting[3]);

		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8)
		{
			return $output;
		}

		/**
		* We're kind of forced to use MD5 here since it's the only
		* cryptographic primitive available in all versions of PHP
		* currently in use.  To implement our own low-level crypto
		* in PHP would result in much worse performance and
		* consequently in lower iteration counts and hashes that are
		* quicker to crack (by non-PHP code).
		*/
		if (PHP_VERSION >= 5)
		{
			$hash = md5($salt . $password, true);
			do
			{
				$hash = md5($hash . $password, true);
			}
			while (--$count);
		}
		else
		{
			$hash = pack('H*', md5($salt . $password));
			do
			{
				$hash = pack('H*', md5($hash . $password));
			}
			while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= self::_hash_encode64($hash, 16, $itoa64);

		return $output;
	}
}
