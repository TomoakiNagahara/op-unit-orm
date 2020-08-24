<?php
/** op-unit-orm:/index.php
 *
 * @created   2017-03-16
 * @version   1.0
 * @package   op-unit-orm
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** use
 *
 */
use OP\Unit;

//	...
include('autoloader.php');

//	...
if(!Unit::Load('sql') ){
	throw new Exception("SQL unit was not found.");
}

//	...
return true;
