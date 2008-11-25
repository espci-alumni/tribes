<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';


	function control()
	{
		$this->get->__1__ || p::forbidden();

		$db = DB();

		$sql = "SELECT contact_id, statut_inscription, contact_confirmed_data, password_token, date_naissance
				FROM contact
				WHERE password_token='{$this->get->__1__}'
					AND password_token_date > NOW() - INTERVAL " . tribes::PENDING_PERIOD . "
					AND statut_inscription != 'accepted'";
		$this->data = $db->queryRow($sql);
		$this->data || p::redirect('index'); //XXX aller à un message d'erreur plus explicite

		$this->data = (object) ((array) $this->data + unserialize($this->data->contact_confirmed_data));

		if (!$this->data->statut_inscription)
		{
			$sql = "UPDATE contact_email SET contact_confirmed=NOW()
					WHERE token='{$this->get->__1__}'";
			$db->exec($sql);

			$sql = "UPDATE contact SET statut_inscription='demande'
					WHERE contact_id={$this->data->contact_id}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => tribes::getAdminEmails()), //XXX remplacer par un système de notification
				'email/user/registration/request',
				$this->data
			);
		}
	}

	protected function composeForm($o, $f, $send)
	{
		$f->add('text', 'adresse');

		$send->attach(
			'adresse', '', ''
		);

		return $o;
	}

	protected function save($data)
	{
		return 'user/registration/confirmation/receipt';
	}
}
