<?php

class extends agent_pForm
{
	protected $requiredAuth = false;

	protected function composeForm($o, $f, $send)
	{
		$f->add('text', 'login');
		$f->add('password', 'password');

		$send->attach(
			'login', 'Veuillez saisir votre identifiant', '',
			'password', 'Veuillez saisir votre mot de passe', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$sql = "SELECT c.contact_id, password
				FROM contact_contact c
					JOIN contact_alias a ON c.contact_id=a.contact_id
				WHERE a.login=" . DB()->quote($data['login']);
		$row = DB()->queryRow($sql);

		if (!$row || !p::matchSaltedHash($data['password'], $row->password)) return 'login/failed';

		$contact_id = $row->contact_id;

		if ($sql = s::flash('confirmed_email_id'))
		{
			$row = new tribes_email($contact_id);
			$row->save(array('contact_confirmed' => true), null, $sql);
		}

		$sql = "SELECT 1 FROM contact_email
				WHERE contact_id={$contact_id}
					AND NOT contact_confirmed
					AND admin_confirmed
					AND is_obsolete<=0";
		if (DB()->queryOne($sql))
		{
			$sql = 'user/email/confirm';
		}
		else
		{
			$sql = s::flash('referer');
			$sql || $sql = 'index';
		}

		s::regenerateId(true, true);
		s::set('contact_id', $contact_id);

		return $sql;
	}
}
