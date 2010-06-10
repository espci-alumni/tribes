<?php

class extends agent_user_step_registration
{
	protected function composeForm($o,$f,$send)
	{
		$o = parent::composeAdresse($o, $f, $send);
		$o = parent::composeEmail($o, $f, $send);

		return $o;
	}

	protected function save($data)
	{
		$this->saveAdresse($data);
		$this->saveEmail($data);

		return parent::save($data);
	}
}
