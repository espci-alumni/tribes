<?php

class extends agent_pForm
{
	function control()
	{
		$this->data = s::get('password_contact_id');
		$this->data || p::forbidden();
	}

	protected function save($data)
	{
		s::free('password_contact_id');

		$contact = new tribes_contact($this->data);
		$contact->save(
			array('token' => p::strongid(8)),
			'user/password/request'
		);

		return 'registration/collision/sent';
	}
}
