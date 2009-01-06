<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	protected static $password_rx = '.{6,}';

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


	protected function composeForm($o, $f, $send)
	{
		$o->login = $this->data->login;

		$f->add('password', 'new_pwd', self::$password_rx);
		$f->add('password', 'con_pwd', self::$password_rx);

		$send->attach(
			'new_pwd', 'Veuillez saisir un mot de passe', 'Le mot de passe saisi est trop court : il doit faire 6 caractères minimum',
			'con_pwd', 'Veuillez confirmer votre mot de passe', ''
		);

		return $o;
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

		s::flash('referer', 'user/edit');

		return array('login', 'Mot de passe mis à jour');
	}
}
