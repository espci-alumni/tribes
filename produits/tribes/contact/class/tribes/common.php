<?php

class tribes_common
{
    const

    ACTION_DELETE = 4,
    ACTION_CONFIRM = 3,
    ACTION_UPDATE = 2,
    ACTION_INSERT = 1;

    public

    $contact_id,
    $confirmed;

    protected

    $table,
    $dataFields = array(),
    $metaFields = array(
        'origine' => 'string',
        'is_obsolete' => 'int',
        'sort_key' => 'int',
        'admin_confirmed' => 'int',
        'contact_confirmed' => 'int',
    ),
    $contactData;


    function __construct($contact_id, $confirmed = false)
    {
        $this->contact_id = (int) $contact_id;
        $this->confirmed = (bool) $confirmed;
        $this->contact_id || $this->contact_id = -1;
    }

    function &fetchRow($select, $row_id = 0)
    {
        if (false !== strpos($select, 'contact_data') && $this->dataFields)
        {
            $select .= ',' . implode(',', $this->dataFields);
        }

        $sql = $this->sqlSelect($select, $row_id);

        $data = DB()->fetchAssoc($sql) or Patchwork::forbidden();

        $this->contactData = array();

        if ($this->dataFields)
            foreach ($this->dataFields as $f)
                isset($data[$f]) && $this->contactData[$f] = $data[$f];

        if (!empty($data['contact_data']) && $sql = unserialize($data['contact_data']))
            $this->contactData = $sql + $this->contactData;

        $this->contactData && $data = array_merge($data, $this->contactData);

        unset($data['contact_data']);

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

    function save($data, $message = null, &$id = 0)
    {
        $db = DB();

        $this->contact_id && $data['contact_id'] = $this->contact_id;

        if ($id) $data[$this->table . '_id'] = $id;
        else if (!empty($data[$this->table . '_id'])) $id = $data[$this->table . '_id'];
        else $data[$this->table . '_id'] =& $id;

        $notice = $data + array('admin_confirmed' => $this->confirmed);

        $meta = $this->filterMeta($data);

        isset($data['contact_id']) && $meta['contact_id'] = (int) $data['contact_id'];
        isset($data[$this->table . '_id']) && $meta[$this->table . '_id'] = (int) $data[$this->table . '_id'];

        empty($this->contactData) || $data += $this->contactData;
        $data = $this->filterData($data);

        if ($data)
        {
            $meta['contact_data'] = $db->quote(serialize($data));

            if (!isset($meta['contact_confirmed']) && $this->contact_id)
            {
                $meta['contact_confirmed'] = $this->contact_id == tribes::getConnectedId() || $this->confirmed;
            }
        }

        if ($this->confirmed)
        {
            isset($meta['admin_confirmed']) || $meta['admin_confirmed'] = true;

            $data = array_merge(
                array_map(array($db, 'quote'), $data),
                $meta
            );

            $meta = array_keys($data);
        }
        else
        {
            $data = array_merge(
                $id ? array() : array_map(array($db, 'quote'), $data),
                $meta
            );

            $meta = array_keys($meta);
        }

        if (empty($data['admin_confirmed'])) unset($data['admin_confirmed']);
        else $data['admin_confirmed'] = 'NOW()';

        if (empty($data['contact_confirmed'])) unset($data['contact_confirmed']);
        else $data['contact_confirmed'] = 'NOW()';

        if ($id)
        {
            $sql = "UPDATE contact_{$this->table}
                    SET {$this->table}_id={$id}";
            foreach ($meta as $k) isset($data[$k]) && $sql .= ",{$k}=" . $data[$k];
            $sql .= " WHERE contact_id={$this->contact_id}
                        AND {$this->table}_id={$id}";
            $action = $db->exec($sql) || empty($data['contact_confirmed']) ? self::ACTION_UPDATE : self::ACTION_CONFIRM;
        }
        else
        {
            empty($data['origine']) && $data['origine'] = "'contact/" . tribes::getConnectedId() . "'";

            $sql = "INSERT INTO contact_{$this->table}
                        (" . implode(',', array_keys($data)) . ")
                    VALUES
                        (" . implode(',', $data) . ")
                    ON DUPLICATE KEY UPDATE contact_id={$this->contact_id}";
            foreach ($meta as $k) isset($data[$k]) && $sql .= ",{$k}=VALUES({$k})";
            $action = $db->exec($sql);
            $action || $action = false;

            self::ACTION_INSERT === $action && $id = $db->lastInsertId();
        }

        if ($action && (null === $message || $message))
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
                WHERE contact_id={$this->contact_id}
                    AND {$this->table}_id={$row_id}";

        if (DB()->exec($sql))
        {
            notification::send("user/{$this->table}", array(
                'contact_id' => $this->contact_id,
                'action' => self::ACTION_DELETE,
                $this->table . '_id' => $row_id,
            ));
        }
    }

    function getDataFields() {return $this->dataFields;}
    function getMetaFields() {return $this->metaFields;}

    protected function filterData($data)
    {
        $table = array();

        foreach ($this->dataFields as $f)
        {
            if (isset($data[$f])) $table[$f] = $data[$f];
        }

        if (!empty($table['ville']))
        {
            if (empty($table['pays']))
            {
                if (false !== $sql = strrpos($table['ville'], ','))
                {
                    $table['pays'] = trim(substr($table['ville'], $sql+1));
                    $table['ville'] = trim(substr($table['ville'], 0, $sql));
                }
                else $table['pays'] = self::$paysDefault;
            }

            $table['city_id'] = geodb::getCityId($table['ville'] . ', ' . $table['pays']);

            if ($table['city_id'] && $this->confirmed)
            {
                $sql = "SELECT 1 FROM city WHERE city_id={$table['city_id']}";

                if (!DB()->fetchColumn($sql))
                {
                    $sql = geodb::getCityInfo($table['city_id']);
                    DB()->insert('city', $sql);
                }
            }
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
            case 'sql': $meta[$k] = $data[$k]; break;
            case 'int': $meta[$k] = (int) $data[$k]; break;
            case 'intNull': $meta[$k] = $data[$k] ? (int) $data[$k] : 'NULL'; break;
            case 'saltedHash': empty($data[$k]) || $meta[$k] = $db->quote(Patchwork::saltedHash($data[$k])); break;
            case 'string': $meta[$k] = $db->quote($data[$k]); break;
            case 'stringNull': $meta[$k] = $data[$k] ? $db->quote($data[$k]) : 'NULL'; break;
            }
        }

        return $meta;
    }

    function updateContactModified($id)
    {
        $sql = "UPDATE contact_contact
                SET contact_modified=NOW()
                WHERE contact_id={$id}";
        DB()->exec($sql);
    }
}
