<?php
/**	op-unit-orm:/Selftest.class.php
 *
 * @created   2018-06-21
 * @license   Apache-2.0
 * @package   op-unit-orm
 * @copyright (C) 2018 Tomoaki Nagahara
 */

/**	Namespace
 *
 */
namespace OP\UNIT\ORM;

/**	Use
 *
 */
use OP\OP_CORE;
use OP\IF_SELFTEST_CONFIG;
use OP\IF_SELFTEST_INSPECTOR;
use OP\OP_CI;

/**	Selftest
 *
 * @created   2018-06-21
 */
class Selftest
{
	/**	trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/**	Config
	 *
	 * @param	 string	 $file
	 * @return	 array	 $config
	 */
	static private function _Config( string $file )
	{
		//	...
		if( empty($file) ){
			return;
		}

		//	...
		$config = include($file);

		//	...
		foreach( $config as $dsn => $databases ){
			//	...
			$temp = null;
			$dsn = parse_url($dsn);
			parse_str($dsn['query'], $temp);
			$dsn = array_merge($dsn, $temp);

			//	...
			IF_SELFTEST_CONFIG::DSN( $dsn['host'], $dsn['scheme'], $dsn['port']);
			IF_SELFTEST_CONFIG::User(['name'=>$dsn['user'],'password'=>$dsn['pass'],'charset'=>$dsn['charset'] ?? 'utf8']);

			//	...
			foreach( $databases as $database => $tables ){
				//	...
				IF_SELFTEST_CONFIG::Database(['name'=>$database]);

				//	...
				foreach( $tables as $table => $columns ){
					//	...
					IF_SELFTEST_CONFIG::Table($table);

					//	...
					foreach( $columns as $field => $column ){
						/*
						//	...
						$field = $type = $length = $null = $default = $comment = null;
						foreach( ['field','type','length','null','default','comment'] as $key ){
							${$key} = $column[$key] ?? null;
						}
						*/

						//	...
						self::_length($column);

						//	...
						IF_SELFTEST_CONFIG::Set('column', $column);

						//	...
						if( ($column['ai'] ?? false) ){
							IF_SELFTEST_CONFIG::Index($field, $field, $field, 'auto incrment');
						}
					}
				}
			}
		}

		//	...
		return IF_SELFTEST_CONFIG::Get();
	}

	/** Length
	 *
	 * @param	&array	 $column
	 */
	static private function _length(&$column)
	{
		//	...
		if( empty($column['values']) ){
			return;
		}

		//	...
		if( is_string($column['values']) ){
			return;
		}

		//	...
		$length = [];

		//	...
		foreach( $column['values'] as /* $index => */ $values ){
			//	...
			if( is_string($values) ){
				$length[] = $values;
				continue;
			}

			//	...
			if( is_string($values['value']) and strlen($values['value']) ){
				$length[] = $values['value'];
			}
		}

		//	...
		$column['length'] = join(',', $length);
	}

	/** Auto
	 *
	 * @param	 string	 $file
	 */
	static function Auto($file)
	{
		//	...
		if(!OP()->Unit()->Load('selftest') ){
			return;
		}

		//	...
		$config = self::_Config($file);

		//	Set configuration.
		IF_SELFTEST_INSPECTOR::Auto($config, null);

		//	...
		while( $message = IF_SELFTEST_INSPECTOR::Error() ){
			printf('<p class="testcase selftest bold error">%s</p>', $message);
		}

		//	...
		IF_SELFTEST_INSPECTOR::Result();

		// ...
		if( OP()->Request('debug') or OP()->Unit()->Notice()->Has() ){
			IF_SELFTEST_INSPECTOR::Debug();
		}
	}
}
