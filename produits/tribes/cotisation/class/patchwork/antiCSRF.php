<?php

class extends self
{
    static function postAlert()
    {
        if (0 === strpos($_SERVER['PATCHWORK_REQUEST'], 'tpe/callback'))
        {
            $_POST =& $GLOBALS['_POST_BACKUP'];
            $_FILES =& $GLOBALS['_FILES_BACKUP'];
        }
        else parent::postAlert();
    }
}
