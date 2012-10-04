<?php 

require_once(__DIR__.'/../../src/Results.php');

use \sql\Results;

class int_ResultsTest extends PHPUnit_Framework_TestCase {
	private $link;
	protected function setUp() {
		$this->link = new mysqli('localhost', 'test-user', 'test-password', 'test');

		$this->link->query('DROP TABLE IF EXISTS php_integration_tests');
		$this->link->query('CREATE TABLE php_integration_tests (
			id int unsigned not null auto_increment,
			primary key(id)
		)');
	}
	protected function tearDown() {
		$this->link->close();
	}

	/**
	 * @test
	 */
	public function getRow_2RowsAreFetched_BothRowsCanBeReturned() {
		$this->link->query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = $this->link->query('select * from php_integration_tests');
		
		$results = new Results($this->link, $sql);
		
		$this->assertEquals(array('id'=> 1), $results->getRow());
		$this->assertEquals(array('id'=> 2), $results->getRow());
	}

	/**
	 * @test
	 */
	public function toArray_2RowsAreFetched_ReturnsAllRowsAsArray() {
		$this->link->query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = $this->link->query('select * from php_integration_tests');
		
		$results = new Results($this->link, $sql);
		
		$this->assertEquals(array(
			array('id'=> 1),
			array('id'=> 2)
		), $results->toArray());
	}

	/**
	 * @test
	 */
	public function getRow_toArrayIsCalledFirst_BothRowsAreStillGettable() {
		$this->link->query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = $this->link->query('select * from php_integration_tests');
		
		$results = new Results($this->link, $sql);
		$results->toArray();
		
		$this->assertEquals(array('id'=> 1), $results->getRow());
		$this->assertEquals(array('id'=> 2), $results->getRow());
	}

	/**
	 * @test
	 */
	public function getRow_ResultsCreatedWithArray_RowsReturnedCorrectly() {
		$results = new Results(array(
			array('id'=> 1),
			array('id'=> 2)
		));
		
		$this->assertEquals(array('id'=> 1), $results->getRow());
		$this->assertEquals(array('id'=> 2), $results->getRow());
	}

	/**
	 * @test
	 */
	public function foreach_resultsAreCreatedWithSQL_iteratesAsExpected() {
		$this->link->query('insert into php_integration_tests (id) values (1),(2)');
		$sql = $this->link->query('select * from php_integration_tests');

		$results = new Results($this->link, $sql);

		$expected = array
		( array('id'=> 1)
		, array('id'=> 2)
		);
		foreach($results as $row) {
			$this->assertEquals(array_shift($expected), $row);
		}
		$this->assertEquals(
		  array()
		, $expected
		, 'It should have shifted the hell out of this array'
		);
	}

	/**
	 * @test
	 */
	public function foreach_resultsAreCreatedWithArray_iteratesAsExpected() {
		$results = new Results(array(
			array('id'=> 1),
			array('id'=> 2)
		));

		$expected = array
		( array('id'=> 1)
		, array('id'=> 2)
		);
		foreach($results as $row) {
			$this->assertEquals(array_shift($expected), $row);
		}
		$this->assertEquals(
		  array()
		, $expected
		, 'It should have shifted the hell out of this array'
		);
	}
}
?>