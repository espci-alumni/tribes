<?php

class extends agent_user_edit
{
	protected function composeForm($o, $f, $send)
	{
		return $this->composeAdresse($o, $f, $send);
	}

	protected function save($data)
	{
		$this->saveAdresse($data);

		return 'user/edit/activite';
	}
}
