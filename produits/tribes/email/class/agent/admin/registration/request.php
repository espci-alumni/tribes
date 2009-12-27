<?php

class extends self
{
	protected function createAccount($contact)
	{
		$CONFIG['tribes.emailDSN'] && self::emailCreateAccount($contact);

		return parent::createAccount($contact);
	}

	static function emailCreateAccount($contact)
	{
		$db = DB($CONFIG['tribes.emailDSN']);

		$domain = substr($CONFIG['tribes.emailDomain'], 1);

		$data = array(
			'user'     => $contact->user,
			'domain'   => $domain,
			'canonic'  => $contact->user !== $contact->login ? $contact->login : null,
			'display'  => $contact->prenom_usuel . ' ' . $contact->nom_usuel . ' - ' . $domain,
//			'quota'    => 52428800,
			'password' => crypt($contact->password),
		);

		$db->autoExecute('postfix_user', $data);

		$prev = '';

		foreach (array('login','user') as $k)
		{
			$alias = str_replace('-', '', $contact->$k);

			if ($alias !== $prev)
			{
				$prev = $alias;

				$data = array(
					'alias' => $alias,
					'domain' => $domain,
					'type' => 'alias',
					'local' => 1,
					'recipient' => $contact->user . '@' . $domain,
					'hyphen' => $alias !== $contact->$k ? $contact->$k : '',
				);

				$db->autoExecute('postfix_alias', $data);
			}
		}

		// TODO : synchro. des alias et email alt. éventuellement déjà existants ?

		return $data;
	}
}
