<?php

class extends agent_login
{
	protected

	$maxage = -1,
	$requiredAuth = false;


	protected function composeForm($o, $f, $send)
	{
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$f->add('name', 'nom_civil');
		$f->add('name', 'prenom_civil');
		$f->add('email', 'email');
		$f->add('password', 'password');

		$send->attach(
			'sexe',         "Veuillez renseigner le champs Mme Mlle M.", '',
			'nom_civil',    "Veuillez renseigner votre nom", '',
			'prenom_civil', "Veuillez renseigner votre prénom", '',
			'email',        "Veuillez renseigner votre email", '',
			'password',     "Veuillez renseigner votre nouveau mot de passe", ''
		);

		return $o;
	}


	protected function save($data)
	{
		$db = DB();

		$sql = self::sqlSelectMatchingContact($data);

		if ($contact = $db->queryOne($sql))
		{
			$token = p::strongid(8);

			$sql = "UPDATE contact_email
					SET token='registration/collision/{$token}',
						token_expires=NOW()+INTERVAL 60 MINUTE,
						is_obsolete=IF(is_obsolete,-1,0)
					WHERE contact_id={$contact}
						AND email=" . DB()->quote($data['email']) . "
						AND contact_confirmed";
			$db->exec($sql);

			return "registration/collision/{$token}";
		}

		$data += array(
			'nom_etudiant'      => $data['nom_civil'],
			'nom_usuel'         => $data['nom_civil'],
			'prenom_usuel'      => $data['prenom_civil'],
			'photo_token'       => p::strongid(8),
			'cv_token'          => p::strongid(8),
			'token'             => 'confirm/registration/' . p::strongid(8),
			'origine'           => 'registration',
			'contact_confirmed' => true,
		);

		$this->data = (object) $data;

		$contact = new tribes_contact(0, false);
		$contact->save($data, 'registration/receipt');

		$data['login'] = $data['email'];
		parent::save($data);

		$data['is_active'] = 1;
		$contact = new tribes_email($contact->contact_id, false);
		$contact->save($data, false);

		return 'user/edit';
	}

	static function sqlSelectMatchingContact($data)
	{
		$pattern = 'REPLACE(%s,"%s","")';
		$sql = sprintf($pattern, '%s', "'");
		$sql = sprintf($pattern, $sql, " ");
		$sql = sprintf($pattern, $sql, "-");

		$pattern = '%s LIKE CONCAT(%s,"%%%%")';
		$pattern = sprintf($pattern, '%1$s', '%2$s') . ' OR ' . sprintf($pattern, '%2$s', '%1$s');
		$sql = sprintf(
				$pattern,
				sprintf($sql, 'prenom_civil'),
				sprintf($sql, DB()->quote($data['prenom_civil']))
			) . ' OR ' . sprintf(
				$pattern,
				sprintf($sql, 'prenom_usuel'),
				sprintf($sql, DB()->quote($data['prenom_civil']))
			);

		$sql = "SELECT c.contact_id
				FROM contact_contact c
					JOIN contact_email e USING (contact_id)
				WHERE e.email=" . DB()->quote($data['email']) . "
					AND ({$sql})
					AND e.contact_confirmed";

		return $sql;
	}
}
