<?php

class extends agent
{
	protected static

	$request_bin  = '/usr/local/bin/sogenactif/request',
	$response_bin = '/usr/local/bin/sogenactif/response',

	$parameters = array(
		// XXX Deux principaux paramètres à ajuster
		// 'merchant_id'      => '012345678901234',
		// 'pathfile'         => '.../pathfile',

		// 'merchant_country' => 'fr',
		// 'currency_code'    => '978', // EUR

		// Si aucun transaction_id n'est affecté, request en génère
		// un automatiquement à partir de heure/minutes/secondes
		// Référez vous au Guide du Programmeur pour
		// les réserves émises sur cette fonctionnalité
		//
		// 'transaction_id' => '123456',


		// Affectation dynamique des autres paramètres
		// Les valeurs proposées ne sont que des exemples
		// Les champs et leur utilisation sont expliqués dans le Dictionnaire des données
		//
		// 'language'            => 'fr',
		// 'payment_means'       => 'CB,2,VISA,2,MASTERCARD,2',
		// 'header_flag'         => 'no',
		// 'capture_day'         => '',
		// 'capture_mode'        => '',
		// 'bgcolor'             => '',
		// 'block_align'         => '',
		// 'block_order'         => '',
		// 'textcolor'           => '',
		// 'receipt_complement'  => '',
		// 'caddie'              => 'mon_caddie',
		// 'customer_id'         => '',
		// 'customer_email'      => '',
		// 'customer_ip_address' => '',
		// 'data'                => '',
		// 'return_context'      => '',
		// 'target'              => '',
		// 'order_id'            => '',
	);

	protected $requiredAuth = false;


	// Methods related to request handling

	protected static function composeTpe($o, $ref, $euro, $email)
	{
		$request_bin = self::$request_bin;
		$p           = self::$parameters;

		$p['amount']                 = $euro * 100;
		$p['normal_return_url']      = p::__BASE__() . 'cotiser/merci?T$=' . p::getAntiCSRFtoken();
		$p['cancel_return_url']      = p::__BASE__() . 'cotiser/paiement/' . $ref . '?T$=' . p::getAntiCSRFtoken();
		$p['automatic_response_url'] = p::__BASE__() . 'tpe/callback';
		$p['customer_id']            = $ref;
		$p['customer_email']         = $email;

		foreach ($p as $k => $v) $request_bin .= ' ' . escapeshellarg("{$k}={$v}");

		$v = explode('!', `{$request_bin}`);
		$o->atos_html = $v[3];

		return $o;
	}
}
