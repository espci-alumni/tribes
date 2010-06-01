<?php

class extends agent_pForm
{
	public $get = '__1__:c:[-_A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$sql = "SELECT contact_id, login, nom_usuel, prenom_usuel
				FROM contact_contact
				WHERE token='{$this->get->__1__}'
					AND token_expires > NOW()";

		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('error/token');

		tribes_email::confirm($this->get->__1__);
	}

	protected function composeForm($o, $f, $send)
	{
		$o->login  = $this->data->login;
		$o->prenom = $this->data->prenom_usuel;
		$o->nom    = $this->data->nom_usuel;

		$f->add('password', 'password');
		$f->add('password', 'con_pwd', array('isdata' => false));

		$send->attach(
			'password', 'Veuillez saisir un nouveau mot de passe', '',
			'con_pwd', 'Veuillez confirmer votre mot de passe', ''
		);

		return $o;
	}

	protected function formIsOk($f)
	{
		if ($f->getElement('password')->getValue() !== $f->getElement('con_pwd')->getValue())
		{
			$f->getElement('con_pwd')->setError('Confirmation échouée');
			return false;
		}

		return true;
	}

	protected function save($data)
	{
		$data['token'] = '';

		$contact = new tribes_contact($this->data->contact_id);
		$contact->save($data, 'user/password/confirmation');

		s::flash('referer', 'user/edit');

		return array('login', 'Mot de passe mis à jour');
	}
}
