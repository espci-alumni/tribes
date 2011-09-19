<?php

class agent_admin extends agent
{
    protected $requiredAuth = 'admin';

    function compose($o)
    {
        $sql = "SELECT COUNT(*) FROM contact_contact WHERE login!='' AND acces!='' AND is_active=1 AND is_obsolete=0";

        $o->nb_inscrits = DB()->queryOne($sql);

        return $o;
    }
}
