<?php

class extends agent_user_step_registration
{
	protected function composeForm($o, $f, $send)
	{
		return $this->composeActivite($o, $f, $send);
	}

	protected function composeActivite($o, $f, $send, $new = false)
	{
		if (isset($_POST['f_statut_activite_1']))
		{
			$f->add('text', 'statut_activite', array(), false)->setValue($_POST['f_statut_activite_1']);
			$send->attach('statut_activite', '', '');
		}

		$this->activites = new loop_edit_contact_activiteStep($f, $send);

		return $o;
	}

	protected function save($data)
	{
		$this->saveActivite($data);

		return parent::save($data);
	}
}
