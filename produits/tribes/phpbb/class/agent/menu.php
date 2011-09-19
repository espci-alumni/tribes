<?php

class extends self
{
    static function __constructStatic()
    {
        self::$onglets['forum'] = array(
            'titre' => 'Forum',
            'linkto' => 'forum/',
        );

        parent::__constructStatic();
    }
}
