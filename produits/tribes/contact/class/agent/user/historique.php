<?php

class extends agent
{
	protected $contact_id;

	function compose($o)
	{
		isset($this->contact_id) || $this->contact_id = $this->connected_id;

		$sql = "SELECT h.*, prenom_usuel, nom_usuel, login
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.contact_id
				WHERE h.contact_id={$this->contact_id}
				ORDER BY historique_id DESC";

		$o->historiques = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	function filterRow($o)
	{
		if ($o->origine_contact_id !== $o->contact_id)
		{
			$sql = "SELECT
						login        AS origine_login,
						prenom_usuel AS origine_prenom,
						nom_usuel    AS origine_nom
					FROM contact_contact
					WHERE contact_id={$o->origine_contact_id}";

			$o = (object) ((array) $o + (array) DB()->queryRow($sql));
		}
		else
		{
			$o->origine_login  = $o->login;
			$o->origine_prenom = $o->prenom_usuel;
			$o->origine_nom    = $o->nom_usuel;
		}

		if ($o->details && $sql = unserialize($o->details))
		{
			$o->details = new loop_array(array($sql), 'filter_rawArray');
		}

		return $o;
	}
}
