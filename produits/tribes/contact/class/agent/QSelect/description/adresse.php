<?php

class extends agent_QSelect
{
    protected

    $template = 'QSelect/Search.js',
    $requiredAuth = false;


    function compose($o)
    {
        $sql = "SELECT description AS VALUE
                FROM contact_adresse
                WHERE description!=''
                GROUP BY description";

        $o->DATA = new loop_sql($sql);

        return $o;
    }
}
