<?

class extends agent_registration
{
	protected function composeForm($o, $f, $send)
	{
		$f->add('name', 'prenom_civil');
		$f->add('email', 'email');
		
		$send->attach(
			'prenom_civil', 'Veuillez renseigner le champ prenom', '',
			'email', 'Veuillez renseigner le champ email', '');

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = self::sqlSelectMatchingContact($data);

		if ($contact = $db->queryRow($sql))
		{
			$sql = "SELECT contact_id, email
				FROM contact_contact c
					JOIN contact_email e USING (contact_id)
				WHERE c.prenom_civil='{$data['prenom_civil']}'
					AND e.email='{$data['email']}'";

			$this->data = DB()->queryOne($sql);

			$contact = new tribes_contact($this->data['contact_id']);
			$contact->save(
				array(
					'token' => 'user/password/' . p::strongid(8),
					'email' => $this->data['email'],
				),
				'user/password/request'
			);

			return 'registration/collision/sent';
		}

		return 'index';
	}
}
