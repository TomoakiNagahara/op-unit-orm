<?php
/**	op-unit-orm:/index.php
 *
 * @created   2017-03-16
 * @license   Apache-2.0
 * @package   op-unit-orm
 * @copyright (C) 2017 Tomoaki Nagahara
 */

/**	Declare strict type
 *
 */
declare(strict_types=1);

/**	Use
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
