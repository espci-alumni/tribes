<?php

class extends agent_registration
{
	function control()
	{
		parent::control();

		tribes::getConnectedId() && p::redirect('cotiser/bulletin');

		$this->data = s::get('cotisation_registration');

		s::flash('referer', 'cotiser/');
	}

	protected function save($data)
	{
		parent::save($data);

		s::set(array(
			'cotisation_contact_id'   => $this->data->contact_id,
			'cotisation_email'        => $this->data->email,
			'cotisation_registration' => $data,
		));

		return 'cotiser/bulletin';
	}
}
