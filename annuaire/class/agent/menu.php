<?php

class agent_menu extends self
{
    static function __init()
    {
        self::$onglets['annuaire'] = array(
            'titre' => 'Annuaire',
            'linkto' => 'annuaire/',
        );

        self::$onglets['atlas'] = array(
            'titre' => 'Atlas',
            'linkto' => 'annuaire/atlas/',
        );

        parent::__init();
    }
}
