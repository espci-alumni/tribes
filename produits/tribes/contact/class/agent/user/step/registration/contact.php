<?php

class extends agent_user_step_registration
{
	protected function composeForm($o,$f,$send)
	{
		return $this->composeContact($o, $f, $send);
	}

	protected function composePhoto($o, $f, $send) {return $o;}
	protected function composeCv   ($o, $f, $send) {return $o;}

	protected function save($data)
	{
		$this->saveContact($data);

		return parent::save($data);
	}
}
