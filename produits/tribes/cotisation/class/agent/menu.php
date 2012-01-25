<?php

class agent_menu extends self
{
    function compose($o)
    {
        $o = parent::compose($o);

        $o->is_cotisant = SESSION::get('is_cotisant');
        $o->cotisation_expires = SESSION::get('cotisation_expires');

        return $o;
    }
}
