<?php

include_once(__DIR__.'/../../index.php');

use \sql\SQLHelper;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperUpdateTest extends PHPUnit_Framework_TestCase {

	/**
	 * @test
	 * @dataProvider provider_update_SingleValueNoWhere_ValueIsSet
	 */
	public function update_SingleValueNoWhere_ValueIsSet($col, $val) {
		$db = $this->createHelper();
		
		$result = $db->update('table')->set(array(
			$col=>$val
		))->toString();
		
		$this->assertEquals("UPDATE `db`.table SET `$col`=\"$val\"", $result);
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
		))->toString();
		
		$this->assertEquals('UPDATE `db`.table SET `a`="A", `b`="B", `c`="C"', $result);
	}

	/**
	 * @test
	 */
	public function update_ValueWithQuote_EscapesTheQuote() {
		$db = $this->createHelper();
		
		$result = $db->update('table')->set(array('a'=> 'b"c'))->toString();
		
		$this->assertEquals('UPDATE `db`.table SET `a`="b""c"', $result);
	}
	
	/**
	 * @test
	 */
	public function update_WhereIsSet_WhereIsIncluded() {
		$db = $this->createHelper();
		
		$result = 
			$db->update('table')
			->set(array('a'=>'b'))
			->where('a=b')->toString();
		
		$this->assertEquals('UPDATE `db`.table SET `a`="b" WHERE a=b', $result);
	}

	/**
	 * @test
	 */
	public function update_PrefixIsSet_TableIsPrefixed() {
		$db = $this->createHelper();
		
		$db->setPrefix('prefix');
		$result = $db->update('table')->set(array('a'=>'b'))->toString();
		
		$this->assertEquals('UPDATE `db`.prefix_table SET `a`="b"', $result);
	}

	/**
	 * @test
	 */
	public function update_UpdateIsRun_TableIsRespected() {
		$db = $this->createHelper();
		
		$result = $db->update('a')->set(array('a'=>'b'))->toString();
		
		$this->assertEquals('UPDATE `db`.a SET `a`="b"', $result);
	}

	/**
	 * @test
	 */
	public function update_ValueIsNull_NullIsInserted() {
		$db = $this->createHelper();
		
		$result = $db->update('a')->set(array('a'=>null))->toString();
		
		$this->assertEquals('UPDATE `db`.a SET `a`=NULL', $result);
	}

	/**
	 * @test
	 * @dataProvider provider_where_ParamsAdded_ParamsUsed
	 */
	public function where_ParamsAdded_ParamsUsed($param, $where) {
		$db = $this->createHelper();

		$result = $db
			->update('table')
			->set(array('a'=> 'b'))
			->where('c=?', $param)
			->toString();

		$this->assertContains('WHERE '.$where, $result);
		$this->assertNotContains('WHERE WHERE', $result);
	}
	public function provider_where_ParamsAdded_ParamsUsed() {
		return array(
			  array(2, 'c="2"')
			, array('', 'c=""')
			, array(null, 'c IS NULL')
		);
	}

	/**
	 * @test
	 */
	public function set_LiteralValues_LiteralsAreNotEscaped() {
		$db = $this->createHelper();
		
		$result = $db
			->update('table')
			->set(array('a'=> 'A1', 'b'=> new \sql\LiteralValue('B2')))
			->toString();
		
		$this->assertContains('`a`="A1", `b`=B2', $result);
	}

	/**
	 * @test
	 */
	public function set_stringGiven_validSql() {
		$db = $this->createHelper();

		$result = $db
			->update('table')
			->set('a=1, b=2')
			->toString();

		$this->assertContains('a=1, b=2', $result);
	}

	/**
	 * @test
	 */
	public function set_stringWithParams_validSql() {
		$db = $this->createHelper();

		$result = $db
			->update('table')
			->set('a=?, b=?', 1, 2)
			->toString();

		$this->assertContains('a="1", b="2"', $result);
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
