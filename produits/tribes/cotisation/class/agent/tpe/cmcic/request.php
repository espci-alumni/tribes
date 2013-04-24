<?php

class agent_tpe_cmcic_request extends agent
{
    protected static

    $url = 'https://paiement.creditmutuel.fr/test/',
    $version = '3.0',
    $langue = 'FR',
    $devise = 'EUR',
    $codeTpe = '0123456', // XXX
    $codeSociete = '', // XXX
    $keyTpe = '0123456789012345678901234567890123456789'; // XXX

    protected $requiredAuth = false;


    // Methods related to request handling

    protected static function composeTpe($o, $ref, $euro, $email)
    {
        $data = array(
            'TPE' => self::$codeTpe,
            'date' => date('d/m/Y:H:i:s', $_SERVER['REQUEST_TIME']),
            'montant' => $euro . self::$devise,
            'reference' => $ref,
            'texte-libre' => "{$ref}-{$email}",
            'version' => self::$version,
            'lgue' => self::$langue,
            'societe' => self::$codeSociete,
            'mail' => $email,
            'nbrech' => '',
            'dateech1' => '',
            'montantech1' => '',
            'dateech2' => '',
            'montantech2' => '',
            'dateech3' => '',
            'montantech3' => '',
            'dateech4' => '',
            'montantech4' => '',
        );

        $data['MAC'] = self::macRequest($data);
        $data['url_retour'] = Patchwork::__BASE__() . 'cotiser/bulletin';
        $data['url_retour_ok' ] = Patchwork::__BASE__() . 'cotiser/merci';
        $data['url_retour_err'] = $data['url_retour'];

        $f = new pForm($o);
        $f->action = self::$url . 'paiement.cgi';
        $f->setPrefix('');

        $send = $f->add('submit', 'send');

        foreach ($data as $k => $v) $f->add('hidden', $k)->setValue($v);

        return $o;
    }

    protected static function macRequest($data)
    {
        $a = sprintf(
            '%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s',
            $data['TPE'],
            $data['date'],
            $data['montant'],
            $data['reference'],
            $data['texte-libre'],
            $data['version'],
            $data['lgue'],
            $data['societe'],
            $data['mail'],
            $data['nbrech'],
            $data['dateech1'],
            $data['montantech1'],
            $data['dateech2'],
            $data['montantech2'],
            $data['dateech3'],
            $data['montantech3'],
            $data['dateech4'],
            $data['montantech4'],
            ''
        );

        return self::computeHmac($a);
    }


    // Generic crypto methods for request and response

    protected static function computeHmac($data)
    {
        return strtolower(hash_hmac('sha1', $data, self::hexStrKey()));
    }

    protected static function hexStrKey()
    {
        $hexStrKey = substr(self::$keyTpe, 0, 38);
        $hexFinal = substr(self::$keyTpe, 38, 2) . '00';

        $cca0 = ord($hexFinal);

        if (70 < $cca0 && $cca0 < 97)
        {
            $hexStrKey .= chr($cca0 - 23) . substr($hexFinal, 1, 1);
        }
        else
        {
            $hexStrKey .= 'M' === substr($hexFinal, 1, 1)
                ? substr($hexFinal, 0, 1) . '0'
                : substr($hexFinal, 0, 2);
        }

        return pack('H*', $hexStrKey);
    }
}
