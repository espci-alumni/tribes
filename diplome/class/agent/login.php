<?php

class agent_login extends self
{
    protected static $sessionFieldsDiplome = ', promotion';

    static function __constructStatic()
    {
        parent::__constructStatic();
        self::$sessionFields .= self::$sessionFieldsDiplome;
    }
}
