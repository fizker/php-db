<?php

require_once(__DIR__.'/../../../src/tokenizers/StatementTokenizer.php');

use \sql\tokenizers\Statement;
use \sql\tokenizers\StatementTokenizer;

class StatementTokenizerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function __toString_noParameters_correctStatementReturned() {
		$statement = new StatementTokenizer('SELECT * FROM a WHERE b="c"');

		$actual = $statement->__toString();

		$this->assertEquals('SELECT * FROM a WHERE b="c"', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_noParameters_correctStatementReturned() {
		$statement = new StatementTokenizer('SELECT * FROM a WHERE b="c"');

		$actual = $statement->resolveParameters(array());

		$this->assertEquals('SELECT * FROM a WHERE b="c"', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_updateStatement_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('UPDATE a SET b=?, c=? WHERE d=? AND e=?');

		$params = array('1', null, 2, null);
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('UPDATE a SET b="1", c=NULL WHERE d="2" AND e IS NULL', trim($actual));
	}
}
