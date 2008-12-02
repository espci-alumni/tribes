<?php

class extends agent_registration
{
	protected $maxage = 0;

	protected function composeForm($f, $send)
	{
		parent::composeForm($f, $send);

		$f->add('text', 'adresse');

		$send->attach(
			'adresse', '', ''
		);
	}

	protected function save($data)
	{
		return '';
	}
}
