<?php

class agent_QSelect_description_adresse extends agent_QSelect
{
    protected

    $template = 'QSelect/Search.js',
    $requiredAuth = false;


    function compose($o)
    {
        $sql = "SELECT description AS VALUE
                FROM contact_adresse
                WHERE description!='' AND is_obsolete<=0 AND admin_confirmed
                GROUP BY description
                ORDER BY COUNT(*) DESC";

        $o->DATA = new loop_sql($sql);

        return $o;
    }
}
