<?php

class extends agent_user_edit
{
	protected function composeForm($o, $f, $send)
	{
		return $this->composeActivite($o, $f, $send);
	}

	protected function save($data)
	{
		return $this->saveActivite($data);
	}
}
