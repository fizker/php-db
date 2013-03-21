<?php

require_once(__DIR__.'/../../../src/tokenizers/StatementTokenizer.php');

use \sql\tokenizers\Statement;
use \sql\tokenizers\StatementTokenizer;

class StatementTokenizerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function getWhere_simpleSelectGiven_whereClauseIsProperlyExtracted() {
		$statement = new StatementTokenizer('SELECT * FROM a WHERE b="c"');

		$where = $statement->getWhere();

		$this->assertEquals(array('b="c"'), $where);
	}

	/**
	 * @test
	 */
	public function getWhere_complexSelectGiven_whereClauseIsProperlyExtracted() {
		$statement = new StatementTokenizer('SELECT * FROM a WHERE b="c" ORDER BY d');

		$where = $statement->getWhere();

		$this->assertEquals(array('b="c"'), $where);
	}
}
