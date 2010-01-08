<?php

class extends agent_pForm
{
	protected $contact_id, $form;

	function compose($o)
	{
		isset($this->contact_id) || $this->contact_id = $this->connected_id;

		return parent::compose($o);
	}

	function composeForm($o, $f, $send)
	{
		$this->form = $f;

		return $this->composeBlocnote($o, $f, $send);
	}

	function composeBlocnote($o, $f, $send)
	{
		$sql = "SELECT h.*, prenom_usuel AS origine_prenom, nom_usuel As origine_nom, login AS origine_login, origine_contact_id, date_contact
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.origine_contact_id
				WHERE h.contact_id={$this->contact_id}
					AND historique='user/blocnote'
					ORDER BY historique_id DESC";

		$o->notes = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	function filterRow($o)
	{
		$sql = unserialize($o->details);

		$o->note = $sql['note'];

		unset($o->details);

		return $o;
	}
}
