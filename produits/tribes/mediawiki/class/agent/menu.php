<?php

class agent_menu extends self
{
    const ACCUEIL_CONNECTED = 'wiki/Accueil';

    static function __init()
    {
        self::$onglets['wiki'] = array(
            'titre' => 'Wiki',
            'linkto' => 'wiki/',
        );

        parent::__init();
    }
}
