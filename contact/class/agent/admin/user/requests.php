<?php

class agent_admin_user_requests extends agent
{
    protected $requiredAuth = 'admin';

    function compose($o)
    {
        $sql = "SELECT
                    sexe,
                    nom_civil,
                    prenom_civil,
                    date_naissance,
                    contact_id,
                    contact_modified
                FROM contact_contact
                WHERE acces!=''
                    AND contact_modified
                    AND admin_confirmed<=contact_modified
                ORDER BY contact_modified";

        $o->contacts = new loop_sql($sql);

        return $o;
    }
}
