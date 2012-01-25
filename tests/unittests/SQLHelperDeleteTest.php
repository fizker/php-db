<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\QueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperDeleteTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function delete_TableIsNotGiven_ThrowsException() {
		$db = $this->createHelper();
		
		$db->delete()->toString();
	}
	
	/**
	 * @test
	 * @dataProvider provider_delete_TableIsGiven_TableIsDeleted
	 */
	public function delete_TableIsGiven_TableIsUsed($table) {
		$db = $this->createHelper();
		
		$result = $db->delete()->from($table)->toString();
		
		$this->assertEquals("DELETE FROM db.$table", $result);
	}
	public function provider_delete_TableIsGiven_TableIsDeleted() {
		return array(
			array('a'),
			array('b')
		);
	}

	/**
	 * @test
	 */
	public function delete_WhereIsGiven_WhereIsIncluded() {
		$db = $this->createHelper();
		
		$result = $db->delete()->from('table')->where('a=b')->toString();
		
		$this->assertEquals('DELETE FROM db.table WHERE a=b', $result);
	}

	/**
	 * @test
	 */
	public function where_ParamsAdded_ParamsUsed() {
		$db = $this->createHelper();
		
		$result = $db
			->delete()
			->from('table')
			->where('a=?', 2)
			->toString();
		
		$this->assertEquals('DELETE FROM db.table WHERE a="2"', $result);
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