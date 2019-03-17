<?php

use Patchwork\Utf8 as u;

class tribes_adresse extends tribes_common
{
    protected

    $table = 'adresse',
    $dataFields = array(
        'description',
        'adresse',
        'ville_avant',
        'ville',
        'ville_apres',
        'pays',
        'email_list',
        'tel_portable',
        'tel_fixe',
        'tel_fax',
    );


    function __construct($contact_id, $confirmed = false)
    {
        parent::__construct($contact_id, $confirmed);

        $this->metaFields['is_shared'] = 'int';
    }

    function save($data, $message = null, &$id = 0)
    {
        if ($this->confirmed) unset($data['sort_key']);

        if ( !empty($data['correspondance'])
          && !empty($data['description'])
          && ($id || !empty($data['adresse_id'])) )
        {
            if (false !== stripos($data['description'], 'perso')) $p = 'pro';
            else if (false !== stripos($data['description'], 'pro')) $p = 'perso';
            else $p = "pro%' OR '%perso";

            $id || $id = $data['adresse_id'];

            $sql = "SELECT perso_adresse_id AS pro, pro_adresse_id AS perso
                    FROM contact_adresse a JOIN contact_contact c USING (contact_id)
                    WHERE adresse_id={$id} AND (description LIKE '%{$p}%')";
            if ($sql = DB()->fetchAssoc($sql))
            {
                $id = $data['adresse_id'] = isset($sql[$p]) ? $sql[$p] : 0;
            }
        }

        $message = parent::save($data, $message, $id);

        if (!empty($data['correspondance']))
        {
            $sql = "UPDATE contact_contact SET corresp_adresse_id={$id}";

            if (isset($p))
            {
                if ('pro' === $p) $sql .= ",perso_adresse_id={$id}";
                if ('perso' === $p) $sql .= ",pro_adresse_id={$id}";
            }

            $sql .= " WHERE contact_id={$this->contact_id}";
            DB()->exec($sql);
        } else if (!$this->confirmed) {
        } else if ((false !== stripos($data['description'], 'perso') && $p = 'perso') || (false !== stripos($data['description'], 'pro') && $p = 'pro')) {
            $sql = "UPDATE contact_contact SET
                        perso_adresse_id=IF(perso_adresse_id={$id},NULL,perso_adresse_id),
                        pro_adresse_id=IF(pro_adresse_id={$id},NULL,pro_adresse_id),
                        {$p}_adresse_id={$id}
                    WHERE contact_id={$this->contact_id}";
            DB()->exec($sql);
        }

        if (!$this->confirmed && (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message))
        {
            $this->updateContactModified($id);
        }

        return $message;
    }

    function delete($row_id)
    {
        $sql = "UPDATE contact_contact SET
                    corresp_adresse_id=IF(corresp_adresse_id={$row_id},NULL,corresp_adresse_id),
                    perso_adresse_id=IF(perso_adresse_id={$row_id},NULL,perso_adresse_id),
                    pro_adresse_id=IF(pro_adresse_id={$row_id},NULL,pro_adresse_id)
                WHERE contact_id={$this->contact_id}";
        DB()->exec($sql);

        parent::delete($row_id);
    }

    protected function filterData($data)
    {
        isset($data['description']) && $data['description'] = u::ucfirst(mb_strtolower($data['description']));

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
