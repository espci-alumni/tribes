<?php

class extends agent_user_secretariat
{
	function compose($o)
	{
		$sql = "SELECT *
				FROM cotisation
				WHERE contact_id={$this->contact_id}
					AND paiement_date
				ORDER BY cotisation_id DESC";
		$o->cotisations = new loop_sql($sql);

		return $o;
	}
}
