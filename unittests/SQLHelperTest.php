<?php

include_once(__DIR__.'/../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\QueryBuilder;

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
	 * @dataProvider provider_select_PrefixIsSet_TablesArePrefixed
	 */
	public function select_PrefixIsSet_TablesArePrefixed($tables, $expectedTables) {
		$db = $this->createHelper();

		$db->setPrefix('prefix');
		$result = $db->select('a')->from($tables)->exec();
		
		$this->assertEquals('SELECT a FROM '.$expectedTables, $result);
	}
	public function provider_select_PrefixIsSet_TablesArePrefixed() {
		return array(
			array('b', 'db.prefix_b'),
			array(array('b', 'c'), 'db.prefix_b, db.prefix_c'),
			array(array('b', 'prefix_c'), 'db.prefix_b, db.prefix_c')
		);
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

	/**
	 * @test
	 */
	public function select_OrderIsGiven_TheDataIsOrdered() {
		$db = $this->createHelper();
		
		$result = $db->select('a')->from('b')->order('a DESC')->exec();
		
		$this->assertEquals('SELECT a FROM db.b ORDER BY a DESC', $result);
	}

	/**
	 * @test
	 * @dataProvider provider_insert_SingleValue_InsertsSingleValue
	 */
	public function insert_SingleValue_InsertsSingleValue($col, $value) {
		$db = $this->createHelper();
		
		$result = $db->insert(array($col=>$value))->into('c')->exec();
		
		$this->assertEquals("INSERT INTO db.c (`$col`) VALUES (\"$value\")", $result);
	}
	public function provider_insert_SingleValue_InsertsSingleValue() {
		return array(
			array('a', 'b'),
			array('c', 'd')
		);
	}
	
	/**
	 * @test
	 */
	public function insert_MultipleValues_InsertsAll() {
		$db = $this->createHelper();
		
		$result = $db->insert(array(
			'a'=>'b',
			'c'=>'d'
		))->into('table')->exec();
		
		$this->assertEquals('INSERT INTO db.table (`a`, `c`) VALUES ("b", "d")', $result);
	}

	/**
	 * @test
	 */
	public function insert_ValueWithQuotes_ItShouldEscapeTheQuotes() {
		$db = $this->createHelper();
		
		$result = $db->insert(array('a'=>'b"c'))->into('table')->exec();
		
		$this->assertEquals('INSERT INTO db.table (`a`) VALUES ("b\"c")', $result);
	}
	
	/**
	 * @test
	 * @dataProvider provider_insert_DatabaseIsSet_DatabaseIsUsed
	 */
	public function insert_DatabaseIsSet_DatabaseIsUsed($database) {
		$db = $this->createHelper();
		
		$db->setDatabase($database);
		$result = $db->insert(array('a'=>'b'))->into('table')->exec();
		
		$this->assertEquals('INSERT INTO '.$database.'.table (`a`) VALUES ("b")', $result);
	}
	public function provider_insert_DatabaseIsSet_DatabaseIsUsed() {
		return array(
			array('db'),
			array('bd')
		);
	}

	/**
	 * @test
	 * @dataProvider provider_insert_PrefixIsSet_PrefixIsUsed
	 */
	public function insert_PrefixIsSet_PrefixIsUsed($table, $expected) {
		$db = $this->createHelper();
		
		$db->setPrefix('prefix');
		$result = $db->insert(array('a'=>'b'))->into($table)->exec();
		
		$this->assertEquals('INSERT INTO db.'.$expected.' (`a`) VALUES ("b")', $result);
	}
	public function provider_insert_PrefixIsSet_PrefixIsUsed() {
		return array(
			array('table', 'prefix_table'),
			array('prefix_table', 'prefix_table')
		);
	}

	/**
	 * @test
	 * @dataProvider provider_update_SingleValueNoWhere_ValueIsSet
	 */
	public function update_SingleValueNoWhere_ValueIsSet($col, $val) {
		$db = $this->createHelper();
		
		$result = $db->update('table')->set(array(
			$col=>$val
		))->exec();
		
		$this->assertEquals("UPDATE db.table SET `$col`=\"$val\"", $result);
	}
	public function provider_update_SingleValueNoWhere_ValueIsSet() {
		return array(
			array('a', 'b'),
			array('c', 'd')
		);
	}

	/**
	 * @test
	 */
	public function update_MultipleValues_AllAreSet() {
		$db = $this->createHelper();
		
		$result = $db->update('table')->set(array(
			'a'=>'A',
			'b'=>'B',
			'c'=>'C'
		))->exec();
		
		$this->assertEquals('UPDATE db.table SET `a`="A", `b`="B", `c`="C"', $result);
	}

	/**
	 * @test
	 */
	public function update_ValueWithQuote_EscapesTheQuote() {
		$db = $this->createHelper();
		
		$result = $db->update('table')->set(array('a'=> 'b"c'))->exec();
		
		$this->assertEquals('UPDATE db.table SET `a`="b\"c"', $result);
	}
	
	/**
	 * @test
	 */
	public function update_WhereIsSet_WhereIsIncluded() {
		$db = $this->createHelper();
		
		$result = 
			$db->update('table')
			->set(array('a'=>'b'))
			->where('a=b')->exec();
		
		$this->assertEquals('UPDATE db.table SET `a`="b" WHERE a=b', $result);
	}

	/**
	 * @test
	 */
	public function update_PrefixIsSet_TableIsPrefixed() {
		$db = $this->createHelper();
		
		$db->setPrefix('prefix');
		$result = $db->update('table')->set(array('a'=>'b'))->exec();
		
		$this->assertEquals('UPDATE db.prefix_table SET `a`="b"', $result);
	}

	/**
	 * @test
	 */
	public function update_UpdateIsRun_TableIsRespected() {
		$db = $this->createHelper();
		
		$result = $db->update('a')->set(array('a'=>'b'))->exec();
		
		$this->assertEquals('UPDATE db.a SET `a`="b"', $result);
	}


	/**
	 * @test
	 */
	public function escape_ValueWithEscapedQuote_ValueShouldBeDoubleEscaped() {
		$db = new TestableQueryBuilder('a');
		
		$result = $db->escape('\"');
		
		$this->assertEquals('\\\\\"', $result);
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

class TestableQueryBuilder extends QueryBuilder {
	public function escape($str) {
		return parent::escape($str);
	}
	public function exec() {
		
	}
}
?>