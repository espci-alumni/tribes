<?php

class agent_menu extends self
{
    static function __init()
    {
        self::$onglets['email'] = array(
            'titre' => 'Email',
            'linkto' => $CONFIG['tribes.webmailUrl'],
        );

        parent::__init();
    }
}
