<?php

class extends agent_user_step
{
	protected function getStep()
	{
		$step = parent::getStep();
		return new tribes_step_registration($step->__toString());
	}
}

