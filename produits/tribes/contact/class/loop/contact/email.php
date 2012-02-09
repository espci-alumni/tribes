<?php

class loop_contact_email extends loop_sql
{
    protected

    $table = 'email',
    $extraSelect = '';


    function __construct($contact_id)
    {
        $sql = $this->extraSelect ? ', ' . $this->extraSelect : '';
        $sql = "SELECT
                    *,
                    {$this->table}_id AS id
                    {$sql}
                FROM contact_{$this->table}
                WHERE contact_id={$contact_id} AND is_obsolete<=0 AND contact_data!='' AND contact_confirmed
                ORDER BY sort_key";

        parent::__construct($sql, array($this, 'filterRow'));
    }

    function filterRow($o)
    {
        if (!empty($o->contact_data) && $v = unserialize($o->contact_data))
            foreach ($v as $k => $v)
                $o->$k = $v;

        foreach ($o as $k => $v)
            if ('0000-00-00' === $v || '0000-00-00 00:00:00' === $v)
                $o->$k = '';

        unset($o->contact_data, $o->token);

        return $o;
    }
}
