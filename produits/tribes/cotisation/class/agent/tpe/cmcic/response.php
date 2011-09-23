<?php

class agent_tpe_cmcic_response extends agent_tpe_cmcic_request
{
    const contentType = 'text/plain';

    protected static function composeResponse($o, $data)
    {
        $data += array(
            'date'        => '',
            'montant'     => '',
            'reference'   => '',
            'texte-libre' => '',
            'code-retour' => '',
            'cvx'         => '',
            'vld'         => '',
            'brand'       => '',
            'status3ds'   => '',
            'numauto'     => '',   // uniquement si transaction faite
            'motifrefus'  => '',   // uniquement si tansaction refusée
            /* uniquement si module antifraude souscrit */
            'originecb'   => '',
            'bincb'       => '',
            'hpancb'      => '',
            'ipclient'    => '',
            'origintr'    => '',
            'veres'       => '',
            'pares'       => '',
        );

        if (isset($data['MAC']) && self::macResponse($data) === strtolower($data['MAC']))
        {
            $o->cdr = 0;
            $token = $data['reference'];
            $euro  = (float) $data['montant'];
            $is_ok = 0;
            $ref   = implode('|', $data);

            switch ($data['code-retour'])
            {
                case 'payetest': $is_ok = -1; break;
                case 'paiement': $is_ok =  1; break;

    /*
                // paiement echelonné
                case 'paiement_pf2':
                case 'paiement_pf3':
                case 'paiement_pf4':
                    break;

                case 'Annulation_pf2':
                case 'Annulation_pf3':
                case 'Annulation_pf4':
                    break;
    */

                default:
                case 'Annulation': $euro = 0; break;
            }

            return array($o, $token, $euro, $is_ok, $ref);
        }
        else return array();
    }

    protected static function macResponse($data)
    {
        $a = sprintf(
            '%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*',
            self::$codeTpe,
            $data['date'],        // date de la commande JJ/MM/AAAA_a_HH:MM:SS
            $data['montant'],     // xxxx.xxEUR  ou XXXXEUR
            $data['reference'],   // reference unique de la commande
            $data['texte-libre'], //
            self::$version,
            $data['code-retour'], // payetest, paiement, Annulation, paiement_pf[N], Annulation_pf[N] (N=2,3 ou 4)
            $data['cvx'],         // oui (cryptogramme VISA/MASTERCARD saisi), non
            $data['vld'],         // date de validité de la carte
            $data['brand'],       // code réseau de la carte : AM, CB, MC, VI ou na
            $data['status3ds'],   // -1, 1, 2, 3, 4
            $data['numauto'],     // numero autorisation de la banque émettrice
            $data['motifrefus'],  // Appel Phonie, Refus, Interdit  (si autorisation refusée
            $data['originecb'],   // code pays de la banque émettrice
            $data['bincb'],       // code BIN de la banque du porteur de la carte
            $data['hpancb'],      // Hachage du numéro de la carte
            $data['ipclient'],    // IP client
            $data['origintr'],    // code pays origine transaction
            $data['veres'],       // Etat 3DSecure du VERes
            $data['pares']        //
        );

        return self::computeHmac($a);
    }
}
