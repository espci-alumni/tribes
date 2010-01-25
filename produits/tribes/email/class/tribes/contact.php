<?php

class extends self
{
	function save($data, $message = null, &$id = 0)
	{
		$message = parent::save($data, $message, $id);

		if (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message)
		{
			$domain = substr($CONFIG['tribes.emailDomain'], 1);

			$db = DB($CONFIG['tribes.emailDSN']);

			unset($user);
			$update = array();
			$aliases = array();

			$sql = "SELECT user
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";

			if (isset($data['password']))
			{
				$update['password'] = crypt($data['password']);
			}

			if ($this->confirmed)
			{
				if (isset($data['prenom_usuel']) && isset($data['nom_usuel']))
				{
					$update['display'] = $data['prenom_usuel'] . ' ' . $data['nom_usuel'] . ' - ' . $domain;
				}

				if (isset($data['login']))
				{
					$aliases[] = $data['login'];

					isset($user) || $user = DB()->queryOne($sql);

					$update['canonic'] = $user !== $data['login'] ? $data['login'] : null;
				}

				for ($i = 0; $i < count(self::$alias); ++$i)
				{
					if (!isset($data[self::$alias[$i][0]])) continue;
					if (!isset($data[self::$alias[$i][1]])) continue;

					$aliases[] = tribes::makeIdentifier($data[self::$alias[$i][0]], '-a-z') . '.' . tribes::makeIdentifier($data[self::$alias[$i][1]], '-a-z');
				}
			}

			if ($aliases)
			{
				isset($user) || $user = DB()->queryOne($sql);

				$sql = "SELECT IF(local,1,-1) FROM postfix_alias WHERE alias='{$user}' AND domain='{$domain}'";
				$is_local = $db->queryOne($sql) >= 0 ? 1 : 0;

				foreach ($aliases as $aliases)
				{
					$alias = str_replace('-', '', $aliases);

					$sql = "INSERT IGNORE INTO postfix_alias (alias,domain,type,local,recipient,hyphen)
							VALUES ('{$alias}','{$domain}','alias',{$is_local},'{$user}@{$domain}','" . ($alias !== $aliases ? $aliases : '') . "')
							ON DUPLICATE KEY UPDATE hyphen=VALUES(hyphen)";
					$db->exec($sql);
				}
			}

			if ($update)
			{
				isset($user) || $user = DB()->queryOne($sql);

				$update['user']   = $user;
				$update['domain'] = $domain;

				foreach ($update as &$user) $user = array('value' => $user);

				$update['user']['key']    = true;
				$update['user']['domain'] = true;

				$db->replace('postfix_user', $update);
			}
		}

		return $message;
	}

	function delete($contact_id)
	{
		$sql = "SELECT GROUP_CONCAT(alias SEPARATOR \"','\")
				FROM contact_alias
				WHERE contact_id={$contact_id}
				GROUP BY contact_id";
		if ($sql = DB()->queryOne($sql))
		{
			$domain = substr($CONFIG['tribes.emailDomain'], 1);
			$sql = "DELETE FROM postfix_alias
					WHERE domain='{$domain}' AND alias IN ('{$sql}')";
			DB($CONFIG['tribes.emailDSN'])->exec($sql);
		}

		parent::delete($contact_id);
	}
}
