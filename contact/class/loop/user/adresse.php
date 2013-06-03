<?php

class loop_user_adresse extends loop_sql
{
    function __construct($contact_id, $hide_email_list = true)
    {
        $sql = "SELECT adresse_id,
                    description,
                    adresse,
                    ville_avant,
                    ville,
                    ville_apres,
                    pays,
                    " . ($hide_email_list ? '' : 'email_list,') . "
                    tel_portable,
                    tel_fixe,
                    tel_fax
                FROM contact_adresse
                WHERE contact_id={$contact_id}
                    AND admin_confirmed
                    AND contact_confirmed
                    AND is_shared
                    AND is_obsolete<=0
                ORDER BY sort_key";

        parent::__construct($sql);
    }
}
