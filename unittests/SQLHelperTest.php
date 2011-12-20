<?php

include_once(__DIR__.'/../src/SQLHelper.php');

use \sql\SQLHelper;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @test
	 */
	public function select_DebugIsSet_ReturnsRawSQL() {
		$db = $this->createHelper();
		$db->setDebug(true);
		
		$result = $db->select('a')->from('b')->where('c')->exec();
		
		$this->assertEquals('SELECT a FROM db.b WHERE c', $result);
	}

	/**
	 * @test
	 */
	public function select_WhereClauseMissing_QueryShouldMatch() {
		$db = $this->createHelper();
		
		$result = $db->select('a')->from('b')->exec();
		
		$this->assertEquals('SELECT a FROM db.b', $result);
	}

	/**
	 * @test
	 */
	public function select_WhatIsArray_fieldsAreNamed() {
		$db = $this->createHelper();
		
		$result = $db->select(array('a'=>'A', 'B'=>'b'))->from('b')->exec();
		
		$this->assertEquals('SELECT A AS a, b AS B FROM db.b', $result);
	}

	/**
	 * @test
	 */
	public function select_WhatIsUnnamedArray_FieldsAreNotNamed() {
		$db = $this->createHelper();
		
		$result = $db->select(array('a','b'))->from('c')->exec();
		
		$this->assertEquals('SELECT a, b FROM db.c', $result);
	}

	/**
	 * @test
	 */
	public function select_WhatIsMixed_SomeFieldsAreNamed() {
		$db = $this->createHelper();
		
		$result = $db->select(array('a','B'=>'b'))->from('c')->exec();
		
		$this->assertEquals('SELECT a, b AS B FROM db.c', $result);
	}

	/**
	 * @test
	 */
	public function select_TablesAreArray_AllAreIncluded() {
		$db = $this->createHelper();
		
		$result = $db->select('a')->from(array('b', 'c'))->exec();
		
		$this->assertEquals('SELECT a FROM db.b, db.c', $result);
	}

	/**
	 * @test
	 */
	public function select_PrefixIsSet_TablesArePrefixed() {
		$db = $this->createHelper();

		$db->setPrefix('prefix');
		$result = $db->select('a')->from('b')->exec();
		
		$this->assertEquals('SELECT a FROM db.prefix_b', $result);
	}

	/**
	 * @test
	 * @dataProvider provider_select_DatabaseIsSet_TablesArePrefixed
	 */
	public function select_DatabaseIsSet_TablesArePrefixed($database) {
		$db = $this->createHelper();
		
		$db->setDatabase($database);
		$result = $db->select('a')->from('b')->exec();
		
		$this->assertEquals("SELECT a FROM $database.b", $result);
	}

	public function provider_select_DatabaseIsSet_TablesArePrefixed() {
		return array(
			array('db'), 
			array('db_1'),
			array('db_2')
		);
	}


	public function createHelper() {
		$db = new SQLHelper(array(
			'db'=> 'db',
			'host'=> 'b',
			'user'=> 'c',
			'pass'=> 'd'
		));
		$db->setDebug(true);
		return $db;
	}
}

?>