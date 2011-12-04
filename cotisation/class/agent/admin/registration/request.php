<?php

class agent_admin_registration_request extends self
{
    static function __init()
    {
        parent::__init();

        self::$mergeTableUpdate['cotisation'] = array();
    }
}
