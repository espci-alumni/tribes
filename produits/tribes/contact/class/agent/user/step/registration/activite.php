<?php

class extends agent_user_step_registration
{
	protected function composeForm($o, $f, $send)
	{
		return $this->composeActivite($o, $f, $send);
	}

	protected function composeActivite($o, $f, $send, $new = false)
	{
		$o = parent::composeActivite($o, $f, $send, true);

		$this->activites = $o->activites = new loop_edit_contact_activiteStep($f, $send);

		return $o;
	}

	protected function save($data)
	{
		$this->saveActivite($data);

		return parent::save($data);
	}
}
