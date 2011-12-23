<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

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
	public function escape_ValueWithEscapedQuote_ValueShouldBeDoubleEscaped() {
		$db = new TestableQueryBuilder('a');
		
		$result = $db->escape('\"');
		
		$this->assertEquals('\\\\\"', $result);
	}

	/**
	 * @test
	 */
	public function toString_execIsCalled_ShouldCallToString() {
		$fakeBuilder = $this->getMockBuilder('\sql\QueryBuilder')
			->disableOriginalConstructor()
			->getMock(array('toString'));
		
		$fakeBuilder->expects($this->atLeastOnce())->method('toString');
		
		$fakeBuilder->exec();
	}

}

class TestableQueryBuilder extends QueryBuilder {
	public function escape($str) {
		return parent::escape($str);
	}
	public function toString() {
		
	}
}
?>