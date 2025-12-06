<?php
/**	op-unit-orm:/autoloader.php
 *
 * @created   2018-02-01
 * @license   Apache-2.0
 * @package   op-unit-orm
 * @copyright (C) 2018 Tomoaki Nagahara
 */
//	...
spl_autoload_register( function($name){
	//	...
	$unit = 'ORM';

	//	...
	$namespace = "OP\UNIT\\$unit";

	//	...
	if( $name === $namespace ){
		$name  =  $unit;
	}else if( strpos($name, $namespace) === 0 ){
		$name = substr($name, strlen($namespace)+1);
	}else{
		return;
	}

	//	...
	$path = __DIR__."/{$name}.class.php";

	//	...
	if( file_exists($path) ){
		include($path);
	}else{
		OP\Notice::Set("Does not exists this file. ($path)");
	}
});
