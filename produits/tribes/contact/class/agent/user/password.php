<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$sql = "SELECT contact_id, login
				FROM contact_contact
				WHERE statut_inscription='accepted'
					AND token='{$this->get->__1__}'
					AND token_expires >= NOW()";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('error/token');

		tribes_email::confirm($this->get->__1__);
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
		$contact = new tribes_contact($this->data->contact_id);
		$contact->save(
			array(
				'password' => p::saltedHash($data['new_pwd']),
				'token' => '',
			),
			'user/password/confirmation'
		);

		return array('user/edit', 'Mot de passe mis à jour');
	}
}
