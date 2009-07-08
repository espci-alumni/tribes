<?php

class extends agent_pForm
{
	function composeForm($o, $f, $send)
	{
		$this->contact_id = $this->connected_id;

		$sql = "SELECT h.*, date_contact
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.origine_contact_id
				WHERE h.contact_id={$this->contact_id}
					AND historique='user/blocnote'
					ORDER BY historique_id DESC";

		$o->notes = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	function save($data)
	{
		$data['contact_id'] = $this->connected_id;

		notification::send('user/blocnote', $data);

		return '';
	}

	function filterRow($o)
	{
		$sql = unserialize($o->details);

		$o->note = $sql['note'];

		unset($o->details);

		return $o;
	}
}

