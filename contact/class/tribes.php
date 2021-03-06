<?php

class tribes
{
    const

    PENDING_PERIOD = '3 DAY',
    MAX_DOUBLON_DISTANCE = 0.5;


    static function getConnectedId()
    {
        return (int) SESSION::get('contact_id');
    }

    static function connectedIsAuth($type)
    {
        $id = self::getConnectedId();

        if (!$id) return false;
        if (-1 === $id) return true;
        if (true === $type) return true;

        if ($type === SESSION::get('acces')) return true;
        if ('admin' === SESSION::get('acces')) return true;

        return false;
    }

    static function startFakeSession()
    {
        if (!self::getConnectedId())
        {
            SESSION::set('contact_id', -1);

            SESSION::regenerateId(false, false);
        }
    }

    static function makeIdentifier($a, $auth_chars = 'a-z')
    {
        $a = Patchwork\Utf8::toASCII($a);
        $a = strtolower($a);
        $a = preg_replace("/[^{$auth_chars}]+/", '', $a);

        return $a;
    }

    static function getDataFields($type)
    {
        $fields = array();

        foreach ((array) $type as $type)
        {
            $type = 'tribes_' . $type;
            $type = new $type(0);
            foreach ($type->getDataFields() as $type)
            {
                $fields[] = $type;
            }
        }

        return $fields;
    }

    static function getDoublonSuggestions($contact_id, $data)
    {
        $doublons = array();
        $distances = array();

        $data = self::buildDoublonReference($data);

        $sql = "SELECT contact_id, " . self::$sqlSelectDoublonReference . "
                FROM contact_contact
                WHERE contact_id!={$contact_id}";

        foreach (DB()->query($sql) as $row)
        {
            // FIXME: this scans the full database. Any idea to filter some rows on MySQL side?

            $row = (object) $row;

            $d = self::getDoublonDistance($data, self::buildDoublonReference($row));

            if ($d <= self::MAX_DOUBLON_DISTANCE)
            {
                $doublons[$row->contact_id . ' '] = self::buildDoublonLabel($row);
                $distances[] = $d;
            }
        }

        array_multisort($distances, $doublons);

        return array_slice($doublons, 0, 10);
    }

    static function getDoublonDistance($a, $b)
    {
        return levenshtein($a, $b) / max(strlen($a), strlen($b));
    }

    protected static $sqlSelectDoublonReference = 'nom_civil, nom_usuel, prenom_civil, prenom_usuel';

    protected static function buildDoublonReference($data)
    {
        return self::makeIdentifier("{$data->nom_civil}.{$data->nom_usuel}.{$data->prenom_civil}.{$data->prenom_usuel}", 'a-z.');
    }

    protected static function buildDoublonLabel($data)
    {
        return $data->nom_usuel . ' ' . $data->prenom_civil . ($data->nom_usuel !== $data->nom_civil ? " ({$data->nom_civil})" : '');
    }
}
