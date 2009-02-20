<?php

class extends agent_user_edit
{
	protected function composeForm($o, $f, $send)
	{
		return $this->composeEmail($o, $f, $send);
	}

	protected function save($data)
	{
		$this->saveEmail($data);

		return 'user/edit/email';
	}
}
