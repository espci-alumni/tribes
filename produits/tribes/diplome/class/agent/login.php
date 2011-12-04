<?php

class agent_login extends self
{
    protected static $sessionFieldsDiplome = ', promotion';

    static function __init()
    {
        parent::__init();
        self::$sessionFields .= self::$sessionFieldsDiplome;
    }
}
