<?php

class loop_contact_email extends loop_sql
{
    protected

    $table = 'email',
    $extraSelect = 'is_active';


    function __construct($contact_id)
    {
        $sql = $this->extraSelect ? ', ' . $this->extraSelect : '';
        $sql = "SELECT
                    {$this->table}_id,
                    {$this->table}_id AS id,
                    is_obsolete,
                    IF(admin_confirmed,admin_confirmed,'') AS admin_confirmed,
                    contact_data
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
                isset($o->$k) || $o->$k = $v;

        unset($o->contact_data);

        return $o;
    }
}
