<?php

class extends agent_user_step_registration
{
	protected function composeForm($o,$f,$send)
	{
		$o = $this->composeContact($o, $f, $send);

		unset($o->f_sexe, $o->f_conjoint_email);

		return $o;
	}

	protected function composePhoto($o, $f, $send) {return $o;}
	protected function composeCv   ($o, $f, $send) {return $o;}

	protected function save($data)
	{
		$this->saveContact($data);

		return parent::save($data);
	}
}
