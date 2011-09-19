<?php

class extends self
{
    static function __constructStatic()
    {
        self::$onglets['sympa'] = array(
            'titre' => 'Sympa',
            'linkto' => 'wws/',
        );

        parent::__constructStatic();
    }
}
