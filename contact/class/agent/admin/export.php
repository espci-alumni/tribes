<?php

class agent_admin_export extends agent
{
    const contentType = '';

    protected $requiredAuth = 'admin';


    function compose($o)
    {
        set_time_limit(0);

        $sql = "SELECT c.*,
                    IF (login!='',CONCAT(login,'{$CONFIG['tribes.emailDomain']}'),COALESCE((SELECT email FROM contact_email WHERE contact_id=c.contact_id AND is_obsolete=0 ORDER BY is_active DESC, contact_confirmed DESC LIMIT 1),'')) as email,
                    (SELECT COUNT(*) FROM contact_activite WHERE contact_id=c.contact_id AND is_obsolete=0 AND (NOT date_fin OR date_fin > NOW())) AS nb_activite,
                    (SELECT COUNT(*) FROM contact_adresse  WHERE contact_id=c.contact_id AND is_obsolete=0) AS nb_adresse
                FROM contact_contact c
                WHERE NOT c.is_obsolete
                GROUP BY c.contact_id
                ORDER BY c.nom_usuel, c.prenom_usuel";

        $db = DB();
        $result = $db->query($sql);
        $count = 0;

        while ($row = $result->fetchRow())
            if ($this->filterRow($row, $count))
                $this->mapRow($row, $count++);

        return $o;
    }

    protected function filterRow(&$row, $count)
    {
        $db = DB();

        if ($row->nb_activite)
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

            $sql = "SELECT ({$sql}) AS organisation, ac.*
                    FROM contact_activite ac
                    WHERE contact_id={$row->contact_id} AND is_obsolete=0 AND (NOT date_fin OR date_fin > NOW())
                    ORDER BY
                        IF(date_fin, date_debut, '9999-12-31') DESC,
                        IF(date_fin, date_fin, date_debut) DESC,
                        activite_id DESC
                    LIMIT 1";

            foreach ($db->queryRow($sql) as $k => $v) $row->{'activite_' . $k} = $v;
        }
        else if (0 === $count)
        {
            $sql = "SELECT '' AS organisation, ac.* FROM contact_activite ac LIMIT 1";
            foreach ($db->queryRow($sql) as $k => $v) $row->{'activite_' . $k} = '';
        }

        if ($row->nb_adresse)
        {
            $sql = "SELECT *
                    FROM contact_adresse
                    WHERE contact_id={$row->contact_id} AND is_obsolete=0
                    ORDER BY is_active DESC, contact_modified DESC
                    LIMIT 1";

            foreach ($db->queryRow($sql) as $k => $v) $row->{'adresse_' . $k} = $v;
        }
        else if (0 === $count)
        {
            $sql = "SELECT * FROM contact_adresse LIMIT 1";
            foreach ($db->queryRow($sql) as $k => $v) $row->{'adresse_' . $k} = '';
        }

        $k = explode(' ',
                'login etape_suivante user password photo_token cv_token cv_text is_obsolete contact_data origine sort_key'
            . ' activite_activite_id activite_contact_id activite_city_id activite_is_obsolete activite_admin_confirmed activite_contact_confirmed activite_contact_modified activite_contact_data activite_origine activite_sort_key activite_is_shared'
            . ' adresse_adresse_id adresse_contact_id adresse_city_id adresse_email_list adresse_is_obsolete adresse_admin_confirmed adresse_contact_confirmed adresse_contact_modified adresse_contact_data adresse_origine adresse_sort_key'
        );

        foreach ($k as $k) unset($row->$k);

        foreach ($row as $k => $v)
        {
            switch ($v)
            {
            case '0000-00-00 00:00:00':
            case '0000-00-00': $row->$k = '';
            }
        }

        return true;
    }

    protected function mapRow($row, $count)
    {
        // Implement me by superposition or specialization
    }
}
