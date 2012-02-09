<?php

use Patchwork\Utf8 as u;

class tribes_adresse extends tribes_common
{
    protected

    $table = 'adresse',
    $dataFields = array(
        'adresse',
        'description',
        'ville_avant',
        'ville',
        'ville_apres',
        'pays',
        'email_list',
        'tel_portable',
        'tel_fixe',
        'tel_fax',
    );

    protected static $paysDefault = 'France';


    function __construct($contact_id, $confirmed = false)
    {
        parent::__construct($contact_id, $confirmed);

        $this->metaFields['is_shared'] = 'int';
    }

    function save($data, $message = null, &$id = 0)
    {
        if ($this->confirmed) unset($data['is_active'], $data['sort_key']);

        $message = parent::save($data, $message, $id);

        if (!$this->confirmed && (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message))
        {
            $this->updateContactModified($id);
        }

        return $message;
    }

    protected function filterData($data)
    {
        $data = parent::filterData($data);

        if (isset($data['ville']))
        {
            if (false !== $sql = strrpos($data['ville'], ','))
            {
                $data['pays'] = trim(substr($data['ville'], $sql+1));
                $data['ville'] = trim(substr($data['ville'], 0, $sql));
            }
            else empty($data['pays']) && $data['pays'] = self::$paysDefault;

            $data['city_id'] = geodb::getCityId($data['ville'] . ', ' . $data['pays']);

            if ($data['city_id'] && $this->confirmed)
            {
                $sql = "SELECT 1 FROM city WHERE city_id={$data['city_id']}";

                if (!DB()->queryOne($sql))
                {
                    $sql = geodb::getCityInfo($data['city_id']);
                    unset($sql['city']);
                    DB()->autoExecute('city', $sql);
                }
            }
        }

        isset($data['description']) && $data['description'] = u::ucfirst(mb_strtolower($data['description']));

        return $data;
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
