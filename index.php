<?php 
spl_autoload_register(function($class) {
	$class = explode('\\', $class);
	if($class[0] !== 'sql') {
		return;
	}
	$class = array_slice($class, 1);
	$class = implode('/', $class);
	include(__DIR__."/src/$class.php");
});
