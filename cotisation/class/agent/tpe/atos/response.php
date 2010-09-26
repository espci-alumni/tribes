<?php

class extends agent_tpe_atos_request
{
	protected static function composeResponse($o, $data)
	{
		if (isset($data['DATA']) && is_string($data['DATA']))
		{
			$data = self::$response_bin
				. ' ' . escapeshellarg('pathfile=' . self::$parameters['pathfile'])
				. ' ' . escapeshellarg('message=' . $data['DATA']);

			$data = `{$data}`;

			if ('!0!' === substr($data, 0, 3))
			{
				$a = explode('!', $data);
				$a = array(
					'code'                => $a[ 1],
					'error'               => $a[ 2],
					'merchant_id'         => $a[ 3],
					'merchant_country'    => $a[ 4],
					'amount'              => $a[ 5],
					'transaction_id'      => $a[ 6],
					'payment_means'       => $a[ 7],
					'transmission_date'   => $a[ 8],
					'payment_time'        => $a[ 9],
					'payment_date'        => $a[10],
					'response_code'       => $a[11],
					'payment_certificate' => $a[12],
					'authorisation_id'    => $a[13],
					'currency_code'       => $a[14],
					'card_number'         => $a[15],
					'cvv_flag'            => $a[16],
					'cvv_response_code'   => $a[17],
					'bank_response_code'  => $a[18],
					'complementary_code'  => $a[19],
					'complementary_info'  => $a[20],
					'return_context'      => $a[21],
					'caddie'              => $a[22],
					'receipt_complement'  => $a[23],
					'merchant_language'   => $a[24],
					'language'            => $a[25],
					'customer_id'         => $a[26],
					'order_id'            => $a[27],
					'customer_email'      => $a[28],
					'customer_ip_address' => $a[29],
					'capture_day'         => $a[30],
					'capture_mode'        => $a[31],
					'data'                => $a[32],
				);

				$token = $a['customer_id'];
				$is_ok = '00' === $a['response_code']) ? 1 : 0;
				$euro  = $is_ok ? (float) $a['amount'] : 0;

				return array($o, $token, $euro, $is_ok, $data);
			}
		}

		return array();
	}
}
