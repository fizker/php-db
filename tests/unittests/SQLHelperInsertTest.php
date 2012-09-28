<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\QueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperInsertTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 * @dataProvider provider_insert_SingleValue_InsertsSingleValue
	 */
	public function insert_SingleValue_InsertsSingleValue($col, $value) {
		$db = $this->createHelper();
		
		$result = $db->insert(array($col=>$value))->into('c')->toString();
		
		$this->assertEquals("INSERT INTO `db`.c (`$col`) VALUES (\"$value\")", $result);
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
		))->into('table')->toString();
		
		$this->assertEquals('INSERT INTO `db`.table (`a`, `c`) VALUES ("b", "d")', $result);
	}

	/**
	 * @test
	 */
	public function insert_ValueWithQuotes_ItShouldEscapeTheQuotes() {
		$db = $this->createHelper();
		
		$result = $db->insert(array('a'=>'b"c'))->into('table')->toString();
		
		$this->assertEquals('INSERT INTO `db`.table (`a`) VALUES ("b""c")', $result);
	}
	
	/**
	 * @test
	 * @dataProvider provider_insert_DatabaseIsSet_DatabaseIsUsed
	 */
	public function insert_DatabaseIsSet_DatabaseIsUsed($database) {
		$db = $this->createHelper();
		
		$db->setDatabase($database);
		$result = $db->insert(array('a'=>'b'))->into('table')->toString();
		
		$this->assertEquals('INSERT INTO `'.$database.'`.table (`a`) VALUES ("b")', $result);
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
		$result = $db->insert(array('a'=>'b'))->into($table)->toString();
		
		$this->assertEquals('INSERT INTO `db`.'.$expected.' (`a`) VALUES ("b")', $result);
	}
	public function provider_insert_PrefixIsSet_PrefixIsUsed() {
		return array(
			array('table', 'prefix_table'),
			array('prefix_table', 'prefix_table')
		);
	}

	/**
	 * @test
	 */
	public function insert_MultipleRows_AllRowsAreInserted() {
		$db = $this->createHelper();
		
		$result = $db->insert(array(
			array('a'=> 'A1', 'b'=> 'B1'),
			array('a'=> 'A2', 'b'=> 'B2')
		))->into('table')->toString();
		
		$this->assertEquals('INSERT INTO `db`.table (`a`, `b`) VALUES ("A1", "B1"), ("A2", "B2")', $result);
	}

	/**
	 * @test
	 */
	public function insert_LiteralValues_LiteralsAreNotEscaped() {
		$db = $this->createHelper();
		
		$result = $db->insert(array(
			array('a'=> 'A1', 'b'=> new \sql\LiteralValue('B1'))
		))->into('table')->toString();
		
		$this->assertContains('("A1", B1)', $result);
	}

	public function createHelper() {
		$db = new SQLHelper(array(
			'db'=> 'db',
			'host'=> 'b',
			'user'=> 'c',
			'pass'=> 'd'
		));
		return $db;
	}
}
?>