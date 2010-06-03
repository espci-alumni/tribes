<?php

class extends self
{
	protected

	$requiredAuth = 'membre',
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

			tribes::connectedIsAuth($this->requiredAuth) || p::forbidden();

			$this->connected_is_admin = tribes::connectedIsAuth('admin');
		}
	}
}
