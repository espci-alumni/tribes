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

		$this->activites = new loop_edit_contact_activiteStep($f, $send);

		return $o;
	}

	protected function save($data)
	{
		while ($b = $this->activites->loop())
		{
			$rpos = $b->f_ville->getDbValue();
			$data = array(
				'adresse'    => '',
				'ville'      => $rpos,
				'is_shared'  => 1,
				'contact_id' => $this->contact_id,

			);

			if (false !== $rpos = strrpos($rpos, ','))
			{
				$data['pays']  = trim(substr($data['ville'], $rpos+1));
				$data['ville'] = trim(substr($data['ville'], 0, $rpos));
			}

			$this->adresse->save($data, null, $_POST[$b->f_adresse_id->getName()]);
		}

		$this->saveActivite($data);

		return parent::save($data);
	}
}
