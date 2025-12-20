<?php
/**	op-unit-orm:/ORM.class.php
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

/**	Namespace
 *
 */
namespace OP\UNIT;

/**	Use
 *
 */
use OP\OP_CORE;
use OP\OP_CI;
use OP\IF_ORM;
use OP\IF_DATABASE;
use OP\IF_ORM_RECORD;

/**	ORM
 *
 * @created   2017-03-16
 */
class ORM implements IF_ORM
{
	/** trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/** DSN
	 *
	 * @var string
	 */
	private $_dsn;

	/** Configuration.
	 *
	 * @var array
	 */
	private $_config;

	/** IF_DATABASE
	 *
	 * @var \OP\UNIT\Database
	 */
	private $_DB;

	/** Insert
	 *
	 * @param	 array	 $config
	 * @return	 integer $ai
	 */
	private function _Insert($config)
	{
		//	...
		if(!$query = OP()->Unit()->SQL()->DML()->Insert($config) ){
			return;
		}

		//	...
		return OP()->Unit()->Database()->Query($query, 'insert');
	}

	/**	Update
	 *
	 * @param	 array	 $config
	 * @return	 integer $count
	 */
	private function _Update($config)
	{
		//	...
		if(!$query = OP()->Unit()->SQL()->DML()->Update($config) ){
			return;
		}

		//	...
		return OP()->Unit()->Database()->Query($query, 'update');
	}

	/** Delete
	 *
	 */
	private function _Delete()
	{

	}

	/** Generate "Record" object.
	 *
	 * @param      string        $qql
	 * @param      bool          $create
	 * @return     IF_ORM_RECORD
	 */
	private function _Record( string $qql, bool $create ) : IF_ORM_RECORD
	{
		//	...
		$label = self::Label();

		//	...
		$option = [];

		//	Force single column record.
		$option['limit'] = 1;

		//	...
		require_once( OP()->Path('asset:/unit/database/QQL.class.php') );
		if(!$parsed = \OP\UNIT\DATABASE\QQL::Parse($qql, $option, $label) ){
			return new ORM\Record('', '', [], [], []);
		}
		if(!$config = OP()->Unit()->Database()->Config($label) ){
			return new ORM\Record('', '', [], [], []);
		}

		//	Fetch table structure.
		$database = $parsed['database'] ?? $config['database'] ?? '';
		$table    = $parsed['table'];
		$table    = trim($table, '`');
		$query    = OP()->Unit()->SQL()->DDL()->Show()->Column( $table, $database, $label );
		$struct   = OP()->Unit()->Database()->SQL($query, 'show', $label);

		//	Create or Fetch.
		if( $create ){
			$record = [];
		}else{
			//	Fetch record.
			$record = OP()->Unit()->Database()->QQL( $qql, $option, $label );
		}

		//	...
		return new ORM\Record( $database, $table, $struct, $record, $config );
	}

	/** Connect to database.
	 *
	 * <pre>
	 * //	1. Connect at URL scheme.
	 * $orm->Connect('mysql://testcase:password@localhost:3306?charset=utf8');
	 *
	 * //	2. Connect at config array.
	 * $config = [
	 *   'driver'   => 'mysql',
	 *   'host'     => 'localhost',
	 *   'port'     => '3306',
	 *   'user'     => 'testcase',
	 *   'password' => 'password',
	 *   'charset'  => 'utf8',
	 * ];
	 * $orm->Connect($config);
	 * </pre>
	 *
	 * @param      string|array $config
	 * @return     boolean      $io
	 */
	static function Connect( string|array $config, string $label='default' ) : bool
	{
		/*
		//	...
		if( $this->_DB ){
			Notice::Set('Already connected. (Instance had database object)');
			return;
		}

		//	Build DSN and save.
		if( is_array($config) ){
			//	...
		}

		//	Parse of DSN.
		if( is_string($config) ){
			//	...
			$this->_dsn = $config;

			//	...
			$config = parse_url($config);
			$config['prod']     = $config['scheme'];
			$config['password'] = $config['pass'];

			//	...
			if( isset($config['query']) ){
				$query = null;
				parse_str($config['query'], $query);
				$config = array_merge($config, $query);
			}
		}

		//	...
		return $this->DB()->Connect($config);
		*/

		//	...
		$io = OP()->Unit()->Database()->Connect($config, $label);

		//	..
		self::Label($label);

		//	...
		return $io;
	}

	/** Set / Get saved PDO label.
	 *
	 * @created    2025-12-01
	 * @param      string     $label
	 * @return     string     $label
	 */
	static function Label( string $label='' ) : string
	{
		//	...
		static $_label = null;

		//	...
		if( $label ){
			$_label = $label;
		}

		//	...
		return $_label;
	}

	/** Configuration.
	 *
	 * @deprecated 2025-12-01
	 * @param null|string $config
	 */
	function Config($config=null)
	{
		/*
		//	...
		if(!$this->_config = include($config) ){
			return;
		}

		//	...
		return $this->_config;
		*/
	}

	/** Get/Set Unit of Database.
	 *
	 * @deprecated 2025-12-01
	 * @param	\OP\UNIT\Database|null	 $DB
	 * @return	\OP\UNIT\Database		 $DB
	 */
	function DB($DB=null) : IF_DATABASE
	{
		/*
		if( $DB ){
			$this->_DB = $DB;
		}else
			if(!$this->_DB ){
				$this->_DB = Unit::Instance('Database');
		}

		return $this->_DB;
		*/
		return OP()->Unit()->Database();
	}

	/** New empty record.
	 *
	 * @param	 string		 $table_name
	 * @return	 ORM\Record	 $record
	 */
	function Create( $table )
	{
		return self::_Record( $table, true );
	}

	/**	Find single record.
	 *
	 * @see \OP\IF_ORM::Find()
	 */
	function Find( string $qql, array $conditions=[] ) : \OP\IF_ORM_RECORD
	{
		return self::_Record($qql, false);
	}

	/** Find multiple records.
	 *
	 * @return	 ORM\Records
	 */
	function Finds( string $qql, array $option=[] )
	{

	}

	/** Save is Insert or auto Update.
	 *
	 * <pre>
	 * RETURN VALUE:
	 *   null:    Token unmatch or Validation failed or Not changed.
	 *   boolean: Updated result.
	 *   number:  Auto increment id.
	 *   string:  Unique primary id.
	 * </pre>
	 *
	 * @param	 ORM\Record $record
	 * @return	 mixed
	 */
	function Save( IF_ORM_RECORD & $record )
	{
		//	...
		if( $form = $record->Form() ){
			//	...
			if(!$form->Token() ){
				$record->Error("Session error: token");
				return;
			}

			//	...
			if(!$form->Validate() ){
				return;
			}

			//	...
			$record->Sets( $form->Values() );
		}

		//	...
		$config = [];
		$config['database'] = $record->Database();
		$config['table']    = $record->Table();
		$config['set']      = $record->Changed();

		//	Get primary key and value.
		$pkey = $record->Pkey();
		$pval = $record->Get($pkey);

		//	...
		unset($config['set'][$pkey]);

		//	...
		if( empty($config['set']) ){
			return 0;
		}

		//	...
		if( strlen($pval) ){
			//	Update
			$config['where'][$pkey] = $pval;
			$config['limit'] = 1;

			//	...
			$result = $this->_Update($config);
		}else{
			//	Insert
			//	Get new insert id.
			$result = $this->_Insert($config);

			//	Set new insert id.
			$record->Set($pkey, $result);

			//	Clear form value.
			$record->Form()->Clear();
		}

		//	...
		if( $result ){
			$record->Changed(true);
		}

		//	...
		return $result;
	}

	/** Delete record.
	 *
	 */
	function Delete()
	{

	}

	/** Generate self-test configuration.
	 *
	 * @param	 string		 $file
	 */
	function Selftest( $file_path )
	{
		if( $file_path ){
			ORM\Selftest::Auto( $file_path );
		}
	}

	/**	For developers.
	 *
	 */
	function Debug()
	{
		if( OP()->Request('debug') ){
			D( $this->DB()->Queries() );
		}
	}
}
