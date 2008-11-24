<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	const PENDING_PERIOD = '4 HOUR';

	function control()
	{
		if ($this->get->__1__)
		{
			$sql = "SELECT * FROM contact
					WHERE password_token=" . DB()->quote($this->get->__1__) . "
						AND password_token_date > NOW() - INTERVAL " . self::PENDING_PERIOD . "
						AND statut_inscription IN ('aucune', 'demande')";

			$this->data = DB()->queryRow($sql);
		}
	
		$this->data || p::redirect('index');
	}

	function compose($o)
	{
		if ($this->data->statut_inscription === 'aucune')
		{
			DB()->exec("UPDATE contact SET statut_inscription='demande' WHERE contact_id={$this->data->contact_id}");

			pMail::sendAgent(
				array('To' => tribes::getAdminEmails()),
				'email/user/registration/request',
				$this->data
			);
		}

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$f->add('text', 'prenom_usuel');

		$send->attach(
			'prenom_usuel', '', ''
		);

		return $o;
	}

	protected function save($data)
	{
		return 'user/registration/confirmation/receipt';
	}
}
