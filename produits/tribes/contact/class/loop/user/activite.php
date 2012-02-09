<?php

class loop_user_activite extends loop_sql
{
    function __construct($contact_id)
    {
        $sql = "SELECT GROUP_CONCAT(
                    organisation
                    ORDER BY af.sort_key
                    SEPARATOR ' / '
                )
                FROM contact_organisation o
                    JOIN contact_affiliation af
                        ON af.organisation_id=o.organisation_id
                            AND af.is_admin_confirmed
                WHERE af.activite_id=ac.activite_id
                GROUP BY ''";

        $sql = "SELECT activite_id,
                    ({$sql}) AS organisation,
                    service,
                    titre,
                    fonction,
                    secteur,
                    IF(date_debut,date_debut,'') AS date_debut,
                    IF(date_fin,date_fin,'') AS date_fin,
                    site_web,
                    keyword,
                    ville,
                    pays
                FROM contact_activite ac
                WHERE contact_id={$contact_id}
                    AND admin_confirmed
                    AND contact_confirmed
                    AND is_shared
                    AND is_obsolete<=0
                ORDER BY
                    IF(date_fin, date_debut, '9999-12-31') DESC,
                    IF(date_fin, date_fin, date_debut) DESC,
                    activite_id DESC";

        parent::__construct($sql);
    }
}
