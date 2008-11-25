<?php

class extends agent_pForm
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		parent::control();

		$sql = "SELECT contact_id, password_token, contact_confirmed_data, prenom_civil, nom_civil, date_naissance
				FROM contact
				WHERE password_token='{$this->get->__1__}'
					AND statut_inscription='demande'";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('index');

		$this->data = (object) ((array) $this->data + unserialize($this->data->contact_confirmed_data));
	}

	protected function composeForm($o, $f, $send)
	{
		$f->add('text', 'prenom_civil');
		$f->add('text', 'nom_civil');
		$f->add('date', 'date_naissance');

		$f->add('select', 'statut_inscription', array(
			'firstItem' => '-- Faites une action --',
			'item' => array('accepted' => 'accepter', 'refused' => 'refuser')
		));

		$send->attach( //XXX ajouter les controles de validité ; factoriser avec registration
			'prenom_civil', '', '',
			'nom_civil', '', '',
			'date_naissance', '', '',
			'statut_inscription', 'faire une action', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT email FROM contact_email
				WHERE contact_id={$this->data->contact_id}
					AND token='{$this->data->password_token}'";
		$email = $db->queryOne($sql);

		if ('accepted' === $data['statut_inscription'])
		{
			//XXX mettre à jour le login également
			//XXX implémenter un mécanisme d'association/fusion avec un contact existant

			$sql = "UPDATE contact
					SET is_active=1,
						admin_confirmed=NOW(),
						password_token_date=NOW()";
			foreach ($data as $k => $v) $sql .= ",{$k}=" . $db->quote($v);
			$sql .= "WHERE contact_id={$this->data->contact_id}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => $email),
				"email/user/registration/accepted",
				array('password_token' => $this->data->password_token)
			);

			$sql = "UPDATE contact_email
					SET is_active=1, admin_confirmed=NOW()
					WHERE contact_id={$this->data->contact_id}
						AND email='{$email}'";
			$db->exec($sql);
		}
		else
		{
			$sql = "UPDATE contact SET password_token=NULL, statut_inscription=''
					WHERE contact_id={$this->data->contact_id}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => $email),
				"email/user/registration/refused"
			);
		}

		return 'user/registration/requests';
	}
}
