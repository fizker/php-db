#!/usr/bin/env php
<?php

$path = get_include_path();
$dir = opendir('lib');
while($file = readdir($dir)) {
	if($file[0] == '.') {
		continue;
	}
	$path .= ':lib/' . $file;
}
set_include_path($path);

$argv[] = '--colors';
$argv[] = 'tests';
$_SERVER['argv'] = $argv;

include('lib/phpunit/phpunit.php');
