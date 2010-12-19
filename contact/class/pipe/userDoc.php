<?php

class
{
	static function php($type, $token, $login, $confirmed)
	{
		$token = explode('.', $token);

		return p::base("user/{$type}/{$token[0]}/" . ($confirmed ? '' : '~') . $login . '.' . ($confirmed ? $token[1] : $token[2]), 1);
	}

	static function js()
	{
		?>/*<script>*/

function($type, $token, $login, $confirmed)
{
	$token = $token.split(/\./g);

	return base('user/' + $type + '/' + $token[0] + '/' + ($confirmed ? '' : '~') + $login + '.' + ($confirmed ? $token[1] : $token[2]), 1);
}

<?php	}
}
