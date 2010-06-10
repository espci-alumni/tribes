<?php

class extends agent_user_step_registration
{
	protected function composeForm($o, $f, $send)
	{
		$o = $this->composeCv($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);
		
		return $o;
	}

	protected function composeActivite($o, $f, $send)
	{
		$o = parent::composeActivite($o, $f, $send);

		$sql = "SELECT `value` AS K, `group` AS G, `value` AS V
				FROM item_lists
				WHERE type='contact/statut'
				ORDER BY sort_key, `group`, `value`";
		$f->add('select', 'statut_activite', array('firstItem' => '- Choisir dans la liste -', 'sql' => $sql));

		$send->attach('statut_activite', $this->connected_is_admin ? '' : 'Veuillez renseigner votre statut principal actuel', '');

		return $o;
	}

	protected function save($data)
	{
		parent::saveActivite($data);
		parent::saveCv($data);

		return parent::save($data);
	}

}	


