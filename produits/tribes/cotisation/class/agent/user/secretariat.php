<?php

class extends self
{
	function composeForm($o, $f, $send)
	{
		$o = $this->composeCotisation($o, $f, $send);

		return parent::composeForm($o, $f, $send);
	}

	function composeCotisation($o, $f, $send)
	{
		$sql = "SELECT h.*, prenom_usuel AS origine_prenom, nom_usuel As origine_nom, login AS origine_login, origine_contact_id, date_contact
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.origine_contact_id
				WHERE h.contact_id={$this->contact_id}
					AND historique='user/cotisation'
				ORDER BY historique_id DESC";

		$o->cotisations = new loop_sql($sql, array($this, 'filterCotisation'));

		return $o;
	}

	function filterCotisation($o)
	{
		$sql = unserialize($o->details);

		$o->cotisation_date = $sql['cotisation_date'];
		$o->cotisation_type = $sql['cotisation_type'];

		unset($o->details);

		return $o;
	}
}
