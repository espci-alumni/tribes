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
		$sql = str_replace('-', '', $data['login']);
		$sql = "SELECT c.contact_id, password
				FROM contact_contact c
					JOIN contact_alias a ON c.contact_id=a.contact_id
				WHERE a.alias=" . DB()->quote($sql);
		$row = DB()->queryRow($sql);

		if (!$row || !p::matchSaltedHash($data['password'], $row->password)) return 'login/failed';

		$contact_id = $row->contact_id;

		if ($sql = s::flash('confirmed_email_id'))
		{
			$row = new tribes_email($contact_id);
			$row->save(array('contact_confirmed' => true), null, $sql);
		}

		$data = array(
			'contact_id' => $contact_id,
			'referer'    => s::flash('referer'),
		);

		$sql = "SELECT 1 FROM contact_email
				WHERE contact_id={$contact_id}
					AND NOT contact_confirmed
					AND admin_confirmed
					AND is_obsolete<=0";
		if (DB()->queryOne($sql))
		{
			$data['iframe_src'] = 'user/email/confirm';
		}

		s::regenerateId(true, true);
		s::set($data);

		return 'index';
	}
}
