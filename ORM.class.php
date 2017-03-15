<?php
/**
 * unit-orm:/ORM.class.php
 *
 * @creation  2017-03-16
 * @version   1.0
 * @package   unit-orm
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2018-02-01
 */
namespace OP\UNIT;

/** ORM
 *
 * @creation  2017-03-16
 * @version   1.0
 * @package   unit-orm
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class ORM
{
	/** trait
	 *
	 */
	use \OP_CORE;

	/** IF_DATABASE
	 *
	 * @var \IF_DATABASE
	 */
	private $_DB;

	/** IF_FORM
	 *
	 * @var \IF_FORM
	 */
	private $_form;

	/** Connect to database.
	 *
	 */
	function Connect($config)
	{
		//	...
		if(!$this->_DB = \Unit::Factory('DB') ){
			return false;
		}

		//	...
		if(!$this->_DB->Connect($config) ){
			return false;
		}
	}

	/** New empty recrod.
	 *
	 * @param	 string		 $table_name
	 * @return	 ORM\Record	 $record
	 */
	function Create($qql)
	{
		//	$select is select configuration array.
		$select = \OP\UNIT\DB\QQL::_Select($qql, [], $this->_DB);

		//	...
		$database = $select['database'] ?? $this->_DB->Database();
		$table    = $select['table'];

		//	...
		$query  = \OP\UNIT\SQL\Show::Column($this->_DB, $database, $table);
		$struct = $this->_DB->Query( $query );

		/* @var $record ORM\Record */
		$record = new ORM\Record($struct);
		$record->Database( $database );
		$record->Table(    $table    );

		//	...
		foreach( $struct as $column ){
			$record->Set( $column['field'], '' );
		}

		//	...
		return $record;
	}

	/** Find single record.
	 *
	 * @param	 string		 $qql
	 * @return	 ORM\Record	 $record
	 */
	function Find($qql, $option=null)
	{
		//	Force single column record.
		$option['limit'] = 1;

		//	$select is select configuration array.
		$select   = \OP\UNIT\DB\QQL::_Select($qql, $option, $this->_DB);
		$database = $select['database'] ?? $this->_DB->Database();
		$table    = $select['table'];

		//	Result single column record.
		$result = \OP\UNIT\DB\QQL::_Execute($select, $this->_DB);

		//	...
		$query  = \OP\UNIT\SQL\Show::Column($this->_DB, $database, $table);
		$struct = $this->_DB->Query( $query );

		/* @var $record ORM\Record */
		$record = new ORM\Record( $struct, $result );
		$record->Database( $database );
		$record->Table(    $table    );

		//	Return "Record" Object.
		return $record;
	}

	/** Find multiple records.
	 *
	 * @return	 ORM\Records
	 */
	function Finds($qql, $option=[])
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
	 * @param  ORM\Record $record
	 * @return mixed
	 */
	function Save($record, $validate=[])
	{
		//	...
		if( $form = $record->Form() ){
			//	...
			if(!$form->Token() ){
				return null;
			}

			//	...
			if(!$record->Validate() ){
				return null;
			}

			//	...
			$record->Sets( $form->Values() );
		}

		//	...
		if(!$record->Changed()){
			return null;
		}

		//	...
		$config = [];
		$config['database'] = $record->Database();
		$config['table']    = $record->Table();
		$config['set']      = $record->Changed();

		//	...
		$pkey = $record->Pkey();
		$pval = $record->Get($pkey);

		//	...
		if( $pval ){
			//	Update
			$config['where'][$pkey] = $pval;
			$config['limit'] = 1;

			//	...
			$pval  = $this->_Update($config) ? true: false;
		}else{
			//	Insert
			unset($config['set'][$pkey]);

			//	...
			$pval = $this->_Insert($config);

			//	...
			$record->Set($pkey, $pval);

			//	...
			if( $form = $record->Form() ){
				$vals = $form->Values();
				$conf = $form->Config();

				//	...
				$form->Clear();

				//	...
				$conf['name'] = ORM\Config::FormName($config['database'], $config['table'], $pval);
				$form->Config($conf);
			}
		}

		//	...
		return $pval;
	}

	function _Insert($config)
	{
		//	...
		$query = \OP\UNIT\SQL\Insert::Get($config, $this->_DB);

		//	...
		return $this->_DB->Query($query, 'insert');
	}

	function _Select()
	{

	}

	function _Update($config)
	{
		//	...
		$query = \OP\UNIT\SQL\Update::Get($config, $this->_DB);

		//	...
		return $this->_DB->Query($query, 'update');
	}

	function _Delete()
	{

	}
}
