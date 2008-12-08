<?php

class
{
	protected

	$table = 'contact',
	$contact_id,
	$confirmed,
	$row_id,

	$dataFields = array(
		'sexe',
		'nom_civil',
		'prenom_civil',
		'nom_usuel',
		'prenom_usuel',
		'nom_etudiant',
		'date_naissance'
	);


	function __construct($contact_id, $confirmed, $row_id = 0)
	{
		$this->contact_id = (int) $contact_id;
		$this->confirmed = (bool) $confirmed;
		$this->row_id = (int) $row_id;
	}

	function &fetchRow($select)
	{
		$sql = $this->sqlSelect($select);

		$data = DB()->queryRow($sql, null, MDB2_FETCHMODE_ASSOC);
		$data || p::forbidden();

		empty($data['contact_confirmed_data']) || $data += unserialize($data['contact_confirmed_data']);

		return $data;
	}

	function sqlSelect($select)
	{
		$row_id = $this->row_id ? $this->row_id : $this->contact_id;

		$sql = "SELECT {$select}" . ($this->confirmed ? '' : ', contact_confirmed_data') . "
				FROM contact_{$this->table}
				WHERE contact_id={$this->contact_id} AND {$this->table}_id={$row_id}";
		
		return $sql;
	}

	function update($data, $metadata)
	{
		$db = DB();

		$noticeData = $data = $this->extractData($data);

		if ($this->confirmed)
		{
			$metadata['admin_confirmed'] = 'NOW()';
		}
		else
		{
			$data = array('contact_confirmed_data' => serialize($data));
		}

		$row_id = $this->row_id ? $this->row_id : $this->contact_id;

		$sql = "UPDATE contact_{$this->table}
				SET contact_id={$this->contact_id},
					is_active=1";
		foreach ($data     as $k => $v) $sql .= ",{$k}=" . $db->quote($v);
		foreach ($metadata as $k => $v) $sql .= ",{$k}=" . $v;
		$sql .= " WHERE contact_id={$this->contact_id} AND {$this->table}_id={$row_id}";
		
		$db->exec($sql);
		
		if (isset($metadata['token'])) $noticeData['token'] = substr($metadata['token'], 1, -1);
		$noticeData['contact_id'] = $this->contact_id;

		notification::send("user/{$this->table}", $noticeData);
	}

	function insert($data, $metadata)
	{
		$db = DB();

		$data = $this->extractData($data);
		!$this->confirmed && $data['contact_confirmed_data'] = serialize($data);

		$sql = "INSERT INTO contact_{$this->table}
				(contact_id,";
		$this->confirmed  && $sql .= "admin_confirmed,";
		!empty($metadata) && $sql .= implode(',', array_keys($metadata)) . ',';
		$sql .= implode(',', array_keys($data)) . ")
				VALUES
				({$this->contact_id},";
		$this->confirmed  && $sql .= "NOW(),";
		!empty($metadata) && $sql .= implode(',', $metadata) . ',';
		$sql .= implode(',', array_map(array($db, 'quote'), $data)) . ")
				ON DUPLICATE KEY UPDATE
					is_obsolete=0,
					is_active=1";
		foreach ($metadata as $k => $v) $sql .= ",{$k}=" . $v;
		!$this->confirmed && $sql .= ",contact_confirmed_data=VALUES(contact_confirmed_data)";

		$db->exec($sql);

		if (isset($metadata['token'])) $data['token'] = substr($metadata['token'], 1, -1);
		if (isset($metadata['password_token'])) $data['token'] = substr($metadata['token'], 1, -1);
		$data['contact_id'] = $this->contact_id;

		notification::send("user/{$this->table}", $data);
	}

	function delete()
	{
		$row_id = $this->row_id ? $this->row_id : $this->contact_id;

		$sql = "UPDATE contact_{$this->table}
				SET is_obsolete=1
				WHERE {$this->table}_id={$row_id}";
		DB()->exec($sql);
	}

	protected function extractData($data)
	{
		$table = array();

		foreach ($this->dataFields as $f)
		{
			if (isset($data[$this->table . '_' . $f])) $table[$f] = $data[$this->table . '_' . $f];
			else if (isset($data[$f])) $table[$f] = $data[$f];
		}

		return $table;
	}
}
