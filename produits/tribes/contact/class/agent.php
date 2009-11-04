<?php

class extends self
{
	protected

	$requiredAuth = true,
	$connected_id = 0,
	$connected_is_admin = false;

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

			$this->connected_is_admin = tribes::isAuth('admin', $this->connected_id);
		}
	}
}
