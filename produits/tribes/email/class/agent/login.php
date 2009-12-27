<?php

class extends self
{
	protected function login($contact)
	{
		$CONFIG['tribes.emailDSN'] && self::emailLogin($contact);

		return parent::login($contact);
	}

	protected static function emailLogin($contact)
	{
		$db = DB($CONFIG['tribes.emailDSN']);

		$domain = substr($CONFIG['tribes.emailDomain'], 1);

		$sql = $db->quote($contact->prenom_usuel . ' ' . $contact->nom_usuel . ' - ' . $domain);
		$sql = "UPDATE postfix_user
			SET modified=modified,
				password='" . crypt($contact->password) . "',
				display={$sql},
				canonic=IF(user!='{$contact->login}','{$contact->login}',null)
			WHERE user='{$contact->user}'
				AND domain='{$domain}'";
		if (!$db->exec($sql))
		{
			agent_admin_registration_request::emailCreateAccount($contact);
		}

		// TODO : synchro. des alias et email alt. éventuellement déjà existants ?
	}
}
