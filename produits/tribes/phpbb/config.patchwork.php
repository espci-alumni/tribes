<?php

#patchwork ../contact

$CONFIG += array(
	'tribes.phpbbDb' => empty($CONFIG['tribes.instanceName']) ? false : ($CONFIG['tribes.instanceName'] . '_phpbb'),
	'tribes.phpbbOffset' => 1000,
	'tribes.phpbbPath' => empty($CONFIG['tribes.phpbbPath']) ? '/forum/' : $CONFIG['tribes.phpbbPath'],
);
