<?php

class extends agent_registration
{
	function control()
	{
		parent::control();

		tribes::getConnectedId() && p::redirect('cotiser/bulletin');
	}

	protected function composeForm($o, $f, $send)
	{
		$o = parent::composeForm($o, $f, $send);

		$send = $f->add('submit', 'send_login');

		$o = agent_login::composeForm($o, $f, $send);

		if ($send->isOn())
		{
			$data = $send->getData();
			$send = agent_login::save($data);

			if (false === $send) {}
			else if ('login/failed' === $send)
			{
				p::redirect('cotiser/failed');
			}
			else
			{
				if (false === strpos($data['login'], '@'))
				{
					$data['login'] .= $CONFIG['tribes.emailDomain'];
				}

				s::set(array(
					'cotisation_email'     => $data['login'],
					'cotisation_next_step' => $data,
				));

				p::redirect('cotiser/bulletin');
			}
		}

		return $o;
	}

	protected function save($data)
	{
		$data = parent::save($data);

		if (false === $data) return false;

		s::set(array(
			'cotisation_contact_id' => $this->data->contact_id,
			'cotisation_email'      => $this->data->email,
			'cotisation_next_step'  => $data,
		));

		return 'cotiser/bulletin';
	}
}
