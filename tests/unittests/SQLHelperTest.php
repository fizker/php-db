<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\builders\QueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperTest extends PHPUnit_Framework_TestCase {
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
			->getMock(array('toString'));
		
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
}

class TestableQueryBuilder extends QueryBuilder {
	public function escape($str) {
		return parent::escape($str);
	}
	
	public $query;
	public function toString() {
		return $this->query;
	}
}
?>