<?php

class extends agent_registration
{
	function control()
	{
		parent::control();

		tribes::getConnectedId() && p::redirect('cotiser/bulletin');

		if ($sql = (int) s::get('cotiser_contact_id'))
		{
			$sql = "SELECT sexe,
						nom_civil,
						prenom_civil,
						date_naissance,
						promotion
					FROM contact_contact
					WHERE contact_id={$sql}";
			$this->data = DB()->queryRow($sql);
			$this->data->email = s::get('cotiser_email');
		}

		s::flash('referer', 'cotiser/');
	}

	protected function save($data)
	{
		parent::save($data);

		s::set('cotiser_contact_id', $this->data->contact_id);
		s::set('cotiser_email',      $this->data->email);

		return 'cotiser/bulletin';
	}
}
