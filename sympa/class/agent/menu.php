<?php

class agent_menu extends self
{
    static function __init()
    {
        self::$onglets['sympa'] = array(
            'titre' => 'Sympa',
            'linkto' => 'wws/',
        );

        parent::__init();
    }
}
