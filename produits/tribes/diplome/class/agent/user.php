<?php

class agent_user extends self
{
    protected static $selectFieldsDiplome = ', ecole, promotion';

    static function __init()
    {
        parent::__init();
        self::$selectFields .= self::$selectFieldsDiplome;
    }
}
