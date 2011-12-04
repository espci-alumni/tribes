<?php

class jquery extends self
{
    static function __init()
    {
        self::$uiLoad .= ' ui.datepicker';

        parent::__init();
    }
}
