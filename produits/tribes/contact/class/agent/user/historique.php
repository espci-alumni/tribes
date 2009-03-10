<?php

class extends agent
{
	public $get = '__1__:i:1';

	protected $requiredAuth = 'admin';

	function compose($o)
	{
		$sql = 'SELECT h.*, prenom_usuel, nom_usuel
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.contact_id';
		$this->get->__1__ && $sql .= " WHERE h.contact_id={$this->get->__1__}";
		$sql .= ' ORDER BY historique_id DESC';

		$o->historiques = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	function filterRow($o)
	{
		if ($o->origine_contact_id !== $o->contact_id)
		{
			$sql = "SELECT prenom_usuel AS origine_prenom_usuel, nom_usuel AS origine_nom_usuel
					FROM contact_contact
					WHERE contact_id={$o->origine_contact_id}";

			$o = (object) ((array) $o + (array) DB()->queryRow($sql));
		}
		else
		{
			$o->origine_nom_usuel    = $o->nom_usuel;
			$o->origine_prenom_usuel = $o->prenom_usuel;
		}

		if ($o->details && $sql = unserialize($o->details))
		{
			$o->details = new loop_array(array($sql), 'filter_rawArray');
		}

		return $o;
	}
}
