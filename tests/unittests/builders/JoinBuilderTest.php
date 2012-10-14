<?php
include_once(__DIR__.'/../../../index.php');

use \sql\SQLHelper;

class JoinBuilderTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function construct_stringGiven_validSql() {
		$join = $this->createHelper()->select('a')->join('b');

		$result = $join->toString();

		$this->assertEquals('CROSS JOIN `db`.b', $result);
	}

	/**
	 * @test
	 */
	public function on_stringGiven_validSql() {
		$join = $this->createHelper()->select('*')->join('b');

		$result = $join
			->on('a.id=b.id')
			->toString();

		$this->assertEquals('INNER JOIN `db`.b ON a.id=b.id', $result);
	}

	/**
	 * @test
	 */
	public function as_stringGiven_validSql() {
		$join = $this->createHelper()->select('*')->join('a');

		$result = $join
			->as('b')
			->toString();

		$this->assertEquals('CROSS JOIN `db`.a AS b', $result);
	}

	/**
	 * @test
	 */
	public function construct_builderGiven_validSql() {
		$db = $this->createHelper();

		$b = $db
			->select('*')
			->from('b');

		$result = $db
			->select('*')
			->from('a')
			->join($b)
			->toString();

		$this->assertEquals('CROSS JOIN (SELECT * FROM `db`.b)', $result);
	}

	/**
	 * @test
	 */
	public function done_called_builderReturned() {
		$db = $this->createHelper();

		$select = $db->select('*')->from('a');
		$result = $select
			->join('b')
			->done();

		$this->assertEquals($select, $result);
	}

	/**
	 * @test
	 */
	public function done_called_validSql() {
		$db = $this->createHelper();

		$select = $db->select('*')->from('a');
		$result = $select
			->join('b')
			->done()
			->toString();

		$this->assertEquals('SELECT * FROM `db`.a CROSS JOIN `db`.b', $result);
	}

	/**
	 * @test
	 */
	public function toString_multipleParams_validSql() {
		$join = $this->createHelper()->select('*')->join('a');

		$result = $join
			->on('c')
			->as('b')
			->toString();

		$this->assertEquals('INNER JOIN `db`.a AS b ON c', $result);
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