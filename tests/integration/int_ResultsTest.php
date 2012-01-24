<?php 

require_once(__DIR__.'/../../src/Results.php');

use \sql\Results;

class int_ResultsTest extends PHPUnit_Framework_TestCase {
	private $link;
	protected function setUp() {
		$this->link = mysql_connect('localhost', 'test-user', 'test-password');
		mysql_select_db('test');

		mysql_query('DROP TABLE IF EXISTS php_integration_tests');
		mysql_query('CREATE TABLE php_integration_tests (
			id int unsigned not null auto_increment,
			primary key(id)
		)');
	}
	protected function tearDown() {
		mysql_close($this->link);
	}

	/**
	 * @test
	 */
	public function getRow_2RowsAreFetched_BothRowsCanBeReturned() {
		mysql_query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = mysql_query('select * from php_integration_tests');
		
		$results = new Results($sql);
		
		$this->assertEquals(array('id'=> 1), $results->getRow());
		$this->assertEquals(array('id'=> 2), $results->getRow());
	}

	/**
	 * @test
	 */
	public function toArray_2RowsAreFetched_ReturnsAllRowsAsArray() {
		mysql_query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = mysql_query('select * from php_integration_tests');
		
		$results = new Results($sql);
		
		$this->assertEquals(array(
			array('id'=> 1),
			array('id'=> 2)
		), $results->toArray());
	}

	/**
	 * @test
	 */
	public function getRow_toArrayIsCalledFirst_BothRowsAreStillGettable() {
		mysql_query('insert into php_integration_tests (id) values (1),(2)');
		
		$sql = mysql_query('select * from php_integration_tests');
		
		$results = new Results($sql);
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
}
?>