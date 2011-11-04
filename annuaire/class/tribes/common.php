<?php

class tribes_common extends self
{
    protected static $sync = false;

    function save($data, $message = null, &$id = 0)
    {
        $message = parent::save($data, $message, $id);

        self::$sync = true;

        return $message;
    }

    function delete($row_id)
    {
        parent::delete($row_id);

        self::$sync = true;
    }

    static function __destructStatic()
    {
        self::$sync && tool_url::touch($CONFIG['tribes.annuaire.syncUrl']);
    }
}
