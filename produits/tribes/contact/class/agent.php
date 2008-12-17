<?php

class extends self
{
	protected

	$requiredAuth = true,
	$connected_id = 0;

	function control()
	{
		if ($this->requiredAuth)
		{
			$this->connected_id = tribes::getConnectedId();

			if (!$this->connected_id)
			{
				s::flash('referer', p::__URI__());
				p::redirect('login');
			}

			tribes::isAuth($this->requiredAuth, $this->connected_id) || p::forbidden();
		}
	}
}
