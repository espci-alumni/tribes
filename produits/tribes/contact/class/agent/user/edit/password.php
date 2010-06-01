<?php

class extends agent_user_edit
{
	protected function composeForm($o, $f, $send)
	{
		$o = $this->composeNewPassword($o, $f, $send);
		return $this->composePassword($o, $f, $send);
	}

	protected function save($data)
	{
		$data['token'] = '';
		$this->contact->save($data);

		return '';
	}
}
