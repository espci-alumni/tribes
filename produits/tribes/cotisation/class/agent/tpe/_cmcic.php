<?php

class extends agent
{
	protected static

	$url     = 'https://paiement.creditmutuel.fr/test/',
	$version = '3.0',
	$langue  = 'FR',
	$devise  = 'EUR',
	$codeTpe = '0123456',                                 // XXX
	$codeSociete = '',                                    // XXX
	$keyTpe = '0123456789012345678901234567890123456789'; // XXX

	protected $requiredAuth = false;


	// Methods related to request handling

	protected static function composeTpe($o, $ref, $euro, $email)
	{
		$data = array(
			'TPE'         => self::$codeTpe,
			'date'        => date('d/m/Y:H:i:s', $_SERVER['REQUEST_TIME']),
			'montant'     => $euro . self::$devise,
			'reference'   => $ref,
			'texte-libre' => '',
			'version'     => self::$version,
			'lgue'        => self::$langue,
			'societe'     => self::$codeSociete,
			'mail'        => $email,
			'nbrech'      => '',
			'dateech1'    => '',
			'montantech1' => '',
			'dateech2'    => '',
			'montantech2' => '',
			'dateech3'    => '',
			'montantech3' => '',
			'dateech4'    => '',
			'montantech4' => '',
		);

		$data['MAC'] = self::macRequest($data);
		$data['url_retour'    ] = p::__BASE__();
		$data['url_retour_ok' ] = p::__BASE__() . 'cotiser/merci';
		$data['url_retour_err'] = p::__BASE__() . 'cotiser/paiement/' . $ref;

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


	// Methods related to response handling

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
			$token = $data['reference'];
			$euro  = (float) $data['montant'];
			$mode  = 'CB';
			$ref   = implode('|', $data);

			switch ($data['code-retour'])
			{
				case 'payetest':
					$mode = 'TST';
				case 'paiement':
					break;

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
				case 'Annulation':
					$euro = 0;
					$mode = 'ERR';
					break;
			}

			return array($token, $euro, $mode, $ref);
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


	// Generic crypto methods for request and response

	protected static function computeHmac($data)
	{
		return strtolower(hash_hmac('sha1', $data, self::hexStrKey()));
	}

	protected static function hexStrKey()
	{
		$hexStrKey = substr(self::$keyTpe, 0, 38);
		$hexFinal  = substr(self::$keyTpe, 38, 2) . '00';

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
