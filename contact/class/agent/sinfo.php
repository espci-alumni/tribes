<?php

class agent_sinfo extends agent
{
    const contentType = 'text/plain';

    public $get = 'sid';

    function control()
    {
        $_COOKIE['SID'] = $this->get->sid;
    }

    function compose($o)
    {
        $s = SESSION::getAll();

        unset($s['password'], $s['saltedPassword'], $s['etape_suivante']);

        echo json_encode($s);

        return $o;
    }
}
