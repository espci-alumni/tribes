<?php

use Patchwork\Utf8 as u;

class tribes_activite extends tribes_common
{
    protected

    $table = 'activite',
    $dataFields = array(
        'ville',
        'pays',
        'service',
        'titre',
        'fonction',
        'secteur',
        'statut',
        'date_debut',
        'date_fin',
        'site_web',
        'keyword',
    );


    function __construct($contact_id, $confirmed = false)
    {
        parent::__construct($contact_id, $confirmed);

        $this->metaFields['is_shared'] = 'int';
    }

    function save($data, $message = null, &$id = 0)
    {
        $db = DB();

        if (!empty($data['keyword']))
        {
            $data['keyword'] = preg_replace("'\s*(?:[,;/]+\s*)+'", ', ', $data['keyword']);
            $data['keyword'] = trim($data['keyword'], ", \t");

            if ($this->confirmed)
            {
                $a = preg_split("'[\s,;/]+'", $data['keyword']);
                $o = array();

                foreach ($a as $a)
                {
                    preg_match("'^....'u", $a) && $o[] = $db->quote($a);
                }

                if ($o)
                {
                    $sql = implode("),('keyword',", $o);
                    $sql = "INSERT INTO item_suggestions VALUES ('keyword',{$sql})
                            ON DUPLICATE KEY UPDATE suggestion=VALUES(suggestion)";

                    $db->exec($sql);
                }
            }
        }

        $message = parent::save($data, $message, $id);

        if (!empty($data['principale']))
        {
            $sql = "UPDATE contact_contact SET principale_activite_id={$id} WHERE contact_id={$this->contact_id}";
            DB()->exec($sql);
        }

        $org_inserted = false;

        if (!empty($data['organisation']))
        {
            $confirmed = (int) (bool) $this->confirmed;

            $org = explode('/', $data['organisation']);
            $org = array_map('trim', $org);
            $org = array_unique($org);

            $o = array();
            $a = array();

            $sql = "DELETE FROM contact_affiliation
                    WHERE activite_id={$id}
                        AND is_admin_confirmed<={$confirmed}";
            $db->exec($sql);

            $counter = 0;

            foreach ($org as $org)
            {
                if ('' === $org) continue;

                $q_org = $db->quote($org);

                $sql = "SELECT organisation_id, organisation, is_obsolete
                        FROM contact_organisation
                        WHERE organisation={$q_org}";

                if ($org_id = $db->fetchAssoc($sql))
                {
                    if ($org_id['is_obsolete'] > 0)
                    {
                        $o[] = $org_id['organisation_id'];
                    }

                    if ($confirmed && $org !== $org_id['organisation'])
                    {
                        $sql = "UPDATE contact_organisation
                                SET organisation={$q_org}
                                WHERE organisation_id={$org_id['organisation_id']}";
                        $db->exec($sql);
                    }

                    $org_id = $org_id['organisation_id'];
                }
                else
                {
                    $sql = 1 - $confirmed;
                    $sql = "INSERT INTO contact_organisation (organisation, is_obsolete)
                            VALUES ({$q_org},{$sql})";
                    $db->exec($sql);
                    $org_id = $db->lastInsertId();
                    $org_inserted = true;
                }

                if (!isset($a[$org_id]))
                {
                    ++$counter;

                    $a[$org_id] = "{$id},{$org_id},0,{$counter}";
                    $confirmed && $a[$org_id] .= "),({$id},{$org_id},1,{$counter}";
                }
            }

            if ($o && $this->confirmed)
            {
                $sql = implode(',', $o);
                $sql = "UPDATE contact_organisation
                        SET is_obsolete=0
                        WHERE organisation_id IN ({$sql})";
                $db->exec($sql);
            }

            $sql = implode('),(', $a);
            $sql = "INSERT INTO contact_affiliation VALUES ({$sql})";

            $db->exec($sql);
        }

        if (!$this->confirmed && (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message || (self::ACTION_CONFIRM === $message && $org_inserted)))
        {
            $this->updateContactModified($id);
        }

        return $message;
    }

    function delete($row_id)
    {
        $sql = "UPDATE contact_contact SET
                    principale_activite_id=IF(principale_activite_id={$row_id},NULL,principale_activite_id)
                WHERE contact_id={$this->contact_id}";
        DB()->exec($sql);

        parent::delete($row_id);
    }

    protected function filterData($data)
    {
        isset($data['service']) && $data['service'] = u::ucfirst($data['service']);

        if (empty($data['statut'])) unset($data['statut']);
        else $data['statut'] = u::ucfirst($data['statut']);

        if (empty($data['fonction'])) unset($data['fonction']);
        else $data['fonction'] = u::ucfirst($data['fonction']);

        if (empty($data['secteur'])) unset($data['secteur']);
        else $data['secteur'] = u::ucfirst($data['secteur']);

        return parent::filterData($data);
    }

    function updateContactModified($id)
    {
        $sql = "UPDATE contact_{$this->table}
                SET contact_modified=NOW()
                WHERE contact_id={$this->contact_id}
                    AND {$this->table}_id={$id}";
        DB()->exec($sql);

        parent::updateContactModified($this->contact_id);
    }
}
