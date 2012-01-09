<?php

// TODO : reprendre l'info "promotion" sans créer de dépendence forte, sinon la documenter

class tribes extends self
{
    protected static $cotisation_type;

    static function getCotisationTypes($contact_id)
    {
        if (!isset(self::$cotisation_type))
        {
            $sql = "SELECT value FROM item_lists WHERE type='cotisation/type' ORDER BY sort_key";
            $result = DB()->query($sql);

            while ($row = $result->fetchRow())
            {
                $c = explode('-', $row->value, 2);

                self::$cotisation_type[$row->value] = $c[1] . ' - ' . $c[0] . ' €';
            }
        }

        return self::$cotisation_type;
    }
}
