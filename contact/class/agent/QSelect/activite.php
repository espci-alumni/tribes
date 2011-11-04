<?php

class agent_QSelect_activite extends agent_QSelect
{
    public $get = '__1__:c:titre';

    protected

    $template = 'QSelect/Search.js',
    $requiredAuth = false;


    function compose($o)
    {
        $sql = $this->get->__1__;

        $sql = "SELECT {$sql} AS VALUE
                FROM contact_activite
                WHERE {$sql}!='' AND is_obsolete<=0 AND admin_confirmed
                GROUP BY {$sql}";

        $o->DATA = new loop_sql($sql);

        return $o;
    }
}
