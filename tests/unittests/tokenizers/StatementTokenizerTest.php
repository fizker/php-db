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

		$this->assertEquals('UPDATE a SET b="1", c=NULL WHERE d="2" AND e IS NULL', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_updateStatement_reversedParameters_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('UPDATE a SET b=?, c=? WHERE ?=d AND ?=e');

		$params = array('1', null, 2, null);
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('UPDATE a SET b="1", c=NULL WHERE "2"=d AND e IS NULL', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_selectStatementWithNotEqualsAndNonNullValues_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('SELECT * FROM table WHERE a!=? AND ?!=b OR c<>? AND ?<>d');

		$params = array('1', '2', '3', '4');
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('SELECT * FROM table WHERE a!="1" AND "2"!=b OR c<>"3" AND "4"<>d', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_selectStatementWithNotEqualsAndNullValues_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('SELECT * FROM table WHERE a!=? AND ?!=b OR c<>? AND ?<>d');

		$params = array(null, null, null, null);
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('SELECT * FROM table WHERE a IS NOT NULL AND b IS NOT NULL OR c IS NOT NULL AND d IS NOT NULL', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_quotedEqualsSignAndNullValues_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('SELECT * FROM table WHERE "="=?');

		$params = array(null);
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('SELECT * FROM table WHERE "=" IS NULL', $actual);
	}

	/**
	 * @test
	 */
	public function resolveParameters_quotedQuestionMarkAndNullValues_replacementsShouldBeCorrect() {
		$statement = new StatementTokenizer('SELECT * FROM table WHERE "?"=?');

		$params = array(null);
		$actual = $statement->resolveParameters($params);

		$this->assertEquals('SELECT * FROM table WHERE "?" IS NULL', $actual);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function resolveParameters_greaterThanAndNullValues_shouldThrow() {
		$statement = new StatementTokenizer('SELECT * FROM table WHERE a<?');

		$params = array(null);
		$statement->resolveParameters($params);
	}
}
