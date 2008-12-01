<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$sql = "SELECT contact_id, login
				FROM contact
				WHERE statut_inscription='accepted'
					AND password_token='{$this->get->__1__}'
					AND password_token_expires >= NOW()";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('error/token');
	}

	function compose($o)
	{
		//XXX mettre un login plus user friendly
		$o->login = $this->data->login;

		return parent::compose($o);
	}

	protected function composeForm($f, $send)
	{
		//XXX ajouter une vérification de la complexité du mot de passe

		$f->add('password', 'new_pwd');
		$f->add('password', 'con_pwd');

		$send->attach(
			'new_pwd', 'Veuillez saisir un mot de passe', '',
			'con_pwd', 'Veuillez confirmer votre mot de passe', ''
		);
	}

	protected function formIsOk($f)
	{
		if ($f->getElement('new_pwd')->getValue() !== $f->getElement('con_pwd')->getValue())
		{
			$f->getElement('con_pwd')->setError('Confirmation échouée');
			return false;
		}

		return true;
	}

	protected function save($data)
	{
		$sql = "UPDATE contact
				SET password='" . p::saltedHash($data['new_pwd']) . "', password_token=NULL
				WHERE password_token='{$this->get->__1__}'";
		DB()->exec($sql);

		$sql = "UPDATE contact_email
				SET contact_confirmed=NOW(), is_obsolete=0
				WHERE token='{$this->get->__1__}'";
		DB()->exec($sql);

		return array('user/edit', 'Mot de passe mis à jour');
	}
}
