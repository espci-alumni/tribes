<?php

class agent_menu extends self
{
    static function __init()
    {
        self::$onglets['forum'] = array(
            'titre' => 'Forum',
            'linkto' => 'forum/',
        );

        parent::__init();
    }
}
