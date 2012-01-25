<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\builders\QueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class QueryBuilderTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function escape_ValueWithEscapedQuote_ValueShouldBeDoubleEscaped() {
		$db = new TestableQueryBuilder('a');
		
		$result = $db->escape('\"');
		
		$this->assertEquals('"\\\\\""', $result);
	}

	/**
	 * @test
	 */
	public function escape_ValueIsString_ValueIsQuoted() {
		$builder = new TestableQueryBuilder('a');
		
		$result = $builder->escape('b');
		
		$this->assertEquals('"b"', $result);
	}

	/**
	 * @test
	 */
	public function escape_ValueIsNull_ValueRemainsNull() {
		$builder = new TestableQueryBuilder('a');
		
		$result = $builder->escape(null);
		
		$this->assertEquals('NULL', $result);
	}

	/**
	 * @test
	 */
	public function toString_execIsCalled_ShouldCallToString() {
		$debugMode = true;
		$fakeBuilder = $this->getMockBuilder('\sql\builders\QueryBuilder')
			->setConstructorArgs(array('database name', 'table prefix', $debugMode))
			->setMethods(array('toString'))
			->getMock();
		
		$fakeBuilder->expects($this->atLeastOnce())->method('toString');
		
		$fakeBuilder->exec();
	}

	
	/**
	 * @test
	 */
	public function queryBuilder_DebugIsSet_ReturnsRawSQL() {
		$debugMode = true;
		// The toString of this class return $query exactly as-is
		$db = new TestableQueryBuilder('db', 'prefix', $debugMode);
		$db->query = 'any query';
		
		$result = $db->exec();
		
		$this->assertEquals('any query', $result);
	}

	/**
	 * @test
	 */
	public function update_WhereUsesParams_ParamsAreInjected() {
		$db = new TestableQueryBuilder('db');
		
		$result = $db->addParams('c=?', array('d'));
		
		$this->assertEquals('c="d"', $result);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function addParams_TooFewParams_Throws() {
		$db = new TestableQueryBuilder('db');
		
		$result = $db->addParams('c=?', array());
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function addParams_TooManyParams_Throws() {
		$db = new TestableQueryBuilder('db');
		
		$result = $db->addParams('c=?', array(1,2));
	}

	/**
	 * @test
	 */
	public function addParams_StringContainsQuotedQuestMark_ParamIsInsertedCorrectly() {
		$db = new TestableQueryBuilder('db');
		
		$result = $db->addParams('b="?" AND c=?', array(2));
		
		$this->assertEquals('b="?" AND c="2"', $result);
	}

	/**
	 * @test
	 */
	public function addParams_ComplexString_ParamsInsertedCorrectly() {
		$db = new TestableQueryBuilder('db');
		
		$result = $db->addParams(
			'?=2 AND b="?" AND c=? AND d=?', 
			array(2, 3, 4)
		);
		
		$this->assertEquals(
			'"2"=2 AND b="?" AND c="3" AND d="4"', 
			$result);
	}
}

class TestableQueryBuilder extends QueryBuilder {
	public function escape($str) {
		return parent::escape($str);
	}
	public function addParams($str, $params) {
		return parent::addParams($str, $params);
	}
	
	public $query;
	public function toString() {
		return $this->query;
	}
}
?>