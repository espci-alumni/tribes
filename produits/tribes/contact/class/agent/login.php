<?php

class extends agent_pForm
{
	protected

	$maxage = -1,
	$requiredAuth = false;

	protected static

	$sessionFields = 'contact_id, password, login, user, nom_usuel, prenom_usuel, etape_suivante, acces';


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

		$sql = array(
			"SELECT contact_id
			 FROM contact_alias
			 WHERE alias=" . DB()->quote($sql),
			"SELECT contact_id
			 FROM contact_email
			 WHERE contact_confirmed
				AND email=" . DB()->quote($data['login'])
		);

		$sql = "SELECT " . self::$sessionFields . "
				FROM contact_contact
					JOIN (({$sql[0]}) UNION ({$sql[1]}) ) u USING (contact_id)
				WHERE password!=''";
		$result = DB()->query($sql);

		while ($row = $result->fetchRow())
			if (p::matchSaltedHash($data['password'], $row->password))
				break;

		if (!$row) return 'login/failed';

		$contact_id = $row->contact_id;

		$row->saltedPassword = $row->password;
		$row->password = $data['password'];
		$row->referer  = s::flash('referer');

		if ($sql = s::flash('confirmed_email_id'))
		{
			$email = new tribes_email($contact->contact_id);
			$email->save(array('contact_confirmed' => true), null, $sql);
		}

		s::regenerateId(true, true);
		s::set($row);

		$this->login($row);

		if ('' !== $row->etape_suivante) return "user/step/{$row->etape_suivante}";

		// TODO: Vérifier qu'on a au moins un email actif pour ce contact

		$sql = "SELECT 1
				FROM contact_email
				WHERE contact_id={$contact->contact_id}
					AND NOT contact_confirmed
					AND admin_confirmed
					AND is_obsolete<=0";
		if (DB()->queryOne($sql)) return 'user/email/confirm';

		return agent_menu::ACCUEIL_CONNECTED;
	}

	protected function login($contact)
	{
		// Hook for superpositions
	}
}
