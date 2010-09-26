<?php

class extends agent
{
	public $get = '__1__:c:[-_A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		tribes_email::confirm("confirm/email/{$this->get->__1__}") || p::redirect('error/token');
	}
}
