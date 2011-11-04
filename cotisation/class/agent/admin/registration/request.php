<?php

class agent_admin_registration_request extends self
{
    static function __constructStatic()
    {
        parent::__constructStatic();

        self::$mergeTableUpdate['cotisation'] = array();
    }
}
