<?php

class extends agent_form
{
	protected $maxage = -1;

	protected function composeForm($o, &$f, &$save)
	{
		//TODO: ajouter les rèlges pour les noms/prenoms
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$f->add('text', 'nom_civil');
		$f->add('text', 'prenom_civil');

		$f->add('select', 'categorie',array(
			'firstItem' => '-- Quelle est votre promotion ? --',
			'sql' => 'SELECT categorie_id AS K, categorie AS V
		   				FROM categorie',
		));

		$f->add('email', 'email');

		$save->attach(
			'sexe', 'Veuillez choisir votre civilité', '',
			'nom_civil', 'Veuillez renseigner votre nom', '',
			'prenom_civil', 'Veuillez renseigner votre prénom', '',
			'email', 'Veuillez renseigner votre email', '',
			'categorie', 'Veuillez renseigner votre promotion', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$db = DB();
		
		$email = strtolower($data['email']);
		unset($data['email']);

		if (!$db->queryOne('SELECT 1 FROM contact_email WHERE email=' . $db->quote($email)))
		{
			$categorie_id = $data['categorie'];
			unset($data['categorie']);

			$data['password_token'] = p::strongid(8);
			$data['password_token_date'] = $data['contact_confirmed'] = date('Y-m-d H:i:s');

			$db->autoExecute('contact', $data);
			$contact_id = $db->lastInsertId();

			$db->autoExecute('contact_categorie', array('contact_id' => $contact_id, 'categorie_id' => $categorie_id));
			$db->autoExecute('contact_email', array('contact_id' => $contact_id, 'email' => $email));

			pMail::sendAgent(
				array('To' => $email),
				'email/user/registration/receipt',
				array('password_token' => $data['password_token'])
			);

			return 'user/registration/receipt';
		}
		else return 'index';
	}
}
