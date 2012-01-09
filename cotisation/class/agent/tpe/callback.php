<?php

class agent_tpe_callback extends agent_tpe_response
{
    function compose($o)
    {
        if ('POST' === $_SERVER['REQUEST_METHOD'])
        {
            if ($o = self::composeResponse($o, $_POST))
            {
                list($o, $token, $euro, $is_ok, $ref) = $o;
                $token = explode('/', $token, 2);

                $this->saveResponse($token[0], $token[1], $euro, $is_ok, $ref) || $o = array();
            }
        }

        return $o;
    }

    protected function saveResponse($type, $token, $euro, $is_ok, $ref)
    {
        if ('C' === $type) return $this->saveCotisation($token, $euro, $is_ok, $ref);
    }

    protected function saveCotisation($token, $euro, $is_ok, $ref)
    {
        $db = DB();

        $data = array('paiement_ref' => $ref);

        if ($is_ok)
        {
            $data += array(
                'paiement_euro' => sprintf('%0.2f', $euro),
                'paiement_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
                'paiement_mode' => 0 < $is_ok ? 'CB' : 'TST',
            );
        }
        else $data['paiement_mode'] = 'ERR';

        if ($db->autoExecute('cotisation', $data, MDB2_AUTOQUERY_UPDATE, 'token=' . $db->quote($token)))
        {
            if ($is_ok)
            {
                $sql = "SELECT * FROM cotisation WHERE token=" . $db->quote($token);
                notification::send('user/cotisation', (array) $db->queryRow($sql));
            }

            return true;
        }
        else return false;
    }
}
