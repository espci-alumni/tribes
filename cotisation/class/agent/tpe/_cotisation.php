<?php

class agent_tpe___x5Fcotisation extends agent_tpe_request
{
    public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

    function compose($o)
    {
        $sql = "SELECT
                    cotisation + soutien - paiement_euro AS euro,
                    token,
                    email
                FROM cotisation
                WHERE token='{$this->get->__1__}'
                HAVING euro > 0";
        $o = DB()->fetchAssoc($sql);

        return $o ? self::composeTpe((object) $o, 'C/' . $o['token'], $o['euro'], $o['email']) : array();
    }
}
