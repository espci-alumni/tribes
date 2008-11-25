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
					AND password_token_date > NOW() - INTERVAL " . tribes::PENDING_PERIOD;
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('index');
	}
	
	protected function composeForm($o, $f, $send)
	{
		$o->login = $this->data->login;

		$f->add('password', 'new_pwd');
		$f->add('password', 'con_pwd');

		$send->attach(
			'new_pwd', 'Veuillez saisir un mot de passe', '',
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
		$sql = "UPDATE contact
				SET password='" . p::saltedHash($data['new_pwd']) . "', password_token=NULL
				WHERE contact_id={$this->data->contact_id}";
		DB()->exec($sql);

		return 'user/edit';
	}
}
