<?php

#patchwork ../contact

$CONFIG += array(
	'tribes.mediaWikiDb'   => empty($CONFIG['tribes.instanceName']) ? false : ($CONFIG['tribes.instanceName'] . '_wiki'),
	'tribes.mediaWikiPath' => empty($CONFIG['tribes.mediaWikiPath']) ? '/wiki/' : $CONFIG['tribes.mediaWikiPath'],
);
