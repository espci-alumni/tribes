<?php

class extends self
{
	function composeForm($o, $f, $send)
	{
		$o = parent::composeForm($o, $f, $send);
		$o = $this->composeCotisation($o, $f, $send);

		return $o;
	}

	protected function composeCotisation($o, $f, $send)
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
