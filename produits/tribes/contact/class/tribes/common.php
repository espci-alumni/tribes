<?php

class
{
	protected

	$table = '',
	$contact_id,
	$confirmed,
	$row_id,

	$dataFields = array(
	),

	$metaFields = array(
		'origine'     => 'string',
		'is_active'   => 'int',
		'is_obsolete' => 'int',
		'sort_key'    => 'int',
		'contact_confirmed' => 'int',
	);


	function __construct($contact_id, $confirmed = 0)
	{
		$this->contact_id = (int) $contact_id;
		$this->confirmed = (bool) $confirmed;
		$this->metaFields['contact_id'] = 'int';
		$this->metaFields[$this->table . '_id'] = 'int';
	}

	function &fetchRow($select, $row_id = 0)
	{
		$sql = $this->sqlSelect($select, $row_id);

		$data = DB()->queryRow($sql, null, MDB2_FETCHMODE_ASSOC);
		$data || p::forbidden();

		empty($data['contact_data']) || $data += unserialize($data['contact_data']);

		return $data;
	}

	function sqlSelect($select, $row_id = 0)
	{
		$row_id || $row_id = $this->contact_id;

		$sql = "SELECT {$select}
				FROM contact_{$this->table}
				WHERE contact_id={$this->contact_id} AND {$this->table}_id={$row_id}
				ORDER BY sort_key";

		return $sql;
	}

	function save($data, $message = null, $id = 0)
	{
		$db = DB();

		$data['contact_id'] = $this->contact_id;
		$id && $data[$this->table . '_id'] = $id;

		empty($data['token']) || $data += array('token_expires' => 'NOW() + INTERVAL ' . tribes::PENDING_PERIOD);

		$notice = array('admin_confirmed' => $this->confirmed);

		foreach ($this->metaFields as $k => $v)
		{
			if ('sql' !== $v && isset($data[$k]))
			{
				$notice[$k] = $data[$k];
			}
		}

		$meta = $this->filterMeta($data);
		$data = $this->filterData($data);

		if ($data && empty($data['origine']))
		{
			$data['origine'] = 'contact/' . tribes::getConnectedId(false);
		}

		$notice += $data;

		if ($data)
		{
			ksort($data);
			$meta['contact_data'] = $db->quote(serialize($data));
			isset($meta['contact_confirmed']) || $meta['contact_confirmed'] = $this->contact_id == tribes::getConnectedId(false);
		}

		isset($meta['contact_confirmed']) && $meta['contact_confirmed'] = $meta['contact_confirmed'] ? 'NOW()' : 0;

		if ($this->confirmed)
		{
			$meta['admin_confirmed'] = 'NOW()';

			$data = array_merge(
				array_map(array($db, 'quote'), $data),
				$meta
			);

			$meta = array_keys($data);
			$meta = array_diff($meta, array('contact_data', 'contact_confirmed'));
		}
		else
		{
			$data = array_merge(
				$id ? array() : array_map(array($db, 'quote'), $data),
				$meta
			);

			$meta = array_keys($meta);
		}

		$meta = array_diff($meta, array('origine'));

		if ($id)
		{
			if (!empty($data['contact_confirmed']))
			{
				$contact_confirmed = $data['contact_confirmed'];
				unset($data['contact_confirmed']);
			}
			else $contact_confirmed = false;

			$sql = "UPDATE contact_{$this->table}
					SET {$this->table}_id={$id}";
			foreach ($meta as $k) isset($data[$k]) && $sql .= ",{$k}=" . $data[$k];
			$sql .= " WHERE {$this->table}_id={$id}";
			$action = $db->exec($sql) || !$contact_confirmed ? 'update' : 'confirm';

			if ($contact_confirmed)
			{
				$sql = "UPDATE contact_{$this->table}
						SET contact_confirmed={$contact_confirmed}
						WHERE {$this->table}_id={$id}";
				$db->exec($sql);
			}
		}
		else
		{
			$sql = "INSERT INTO contact_{$this->table}
						(" . implode(',', array_keys($data)) . ")
					VALUES
						(" . implode(',', $data) . ")
					ON DUPLICATE KEY UPDATE contact_id={$this->contact_id}";
			foreach ($meta as $k) $sql .= ",{$k}=VALUES({$k})";
			$action = 2 === $db->exec($sql) ? 'update' : 'insert';
		}

		if (null === $message || $message)
		{
			is_string($message) || $message = "user/{$this->table}";

			$notice['action'] = $action;

			notification::send($message, $notice);
		}

		return $action;
	}

	function delete($row_id)
	{
		$sql = "UPDATE contact_{$this->table}
				SET is_obsolete=1
				WHERE {$this->table}_id={$row_id}";
		DB()->exec($sql);
	}

	protected function filterData($data)
	{
		$table = array();

		foreach ($this->dataFields as $f)
		{
			if (isset($data[$f])) $table[$f] = $data[$f];
		}

		return $table;
	}

	protected function filterMeta($data)
	{
		$db = DB();

		$meta = array();

		foreach ($this->metaFields as $k => $v)
		{
			if (isset($data[$k])) switch ($v)
			{
			case 'sql'       : $meta[$k] = $data[$k]; break;
			case 'int'       : $meta[$k] = (int) $data[$k]; break;
			case 'string'    : $meta[$k] = $db->quote($data[$k]); break;
			case 'stringNull': $meta[$k] = $data[$k] ? $db->quote($data[$k]) : 'NULL'; break;
			}
		}

		return $meta;
	}
}
