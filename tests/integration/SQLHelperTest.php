<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\builders\QueryBuilder;

/**
 * NOTE: The tests requires a specific database setup!
 * It is expected to have a mysql database at localhost
 * called test, with a user called test-user and a password test-password.
 * It will remove any table named php_integration_tests if it exists.
 *
 * These are the only external requirements.
 */
class SQLHelperIntegrationTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->link = new mysqli('localhost', 'test-user', 'test-password', 'test');
		$this->link->query('DROP TABLE IF EXISTS php_integration_tests');
		$this->link->query('CREATE TABLE php_integration_tests (
			id int UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(200),
			has_default VARCHAR(10) DEFAULT "val ue \' """,
			PRIMARY KEY(id)
		)');
	}

	protected function tearDown() {
		$this->link->close();
	}

	/**
	 * This is a smoke test to ensure that if the db-connection fails, 
	 * only one error will be shown.
	 * 
	 * Without this, all tests would fail, and the real 
	 * error would be harder to trace
	 * 
	 * @test
	 */
	public function mysql_setup() {
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function exec_MySQL_ZeroRowsInDatabase_ReturnsZeroRows() {
		$link = new mysqli('localhost', 'test-user', 'test-password', 'test');

		$db = new TestableIntegrationQueryBuilder($link, 'SELECT * FROM php_integration_tests');
		
		$result = $db->exec();
		
		$this->assertEquals(0, $result->length());
		$link->close();
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function exec_MySQL_TwoRowsInDatabase_ReturnsTwoRows() {
		$link = new mysqli('localhost', 'test-user', 'test-password', 'test');
		$link->query('INSERT INTO php_integration_tests (id, name) VALUES (1, "bum"), (2, "bang")');
		
		$db = new TestableIntegrationQueryBuilder($link, 'SELECT * FROM php_integration_tests');
		
		$result = $db->exec();
		
		$this->assertEquals(2, $result->length());
		$link->close();
	}
	
	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function insert_DataIsGiven_TheRowIsInserted() {
		$db = $this->createHelper();
		
		$db->insert(array(
			'id'=> '1',
			'name'=> 'a'
		))->into('php_integration_tests')->exec();

		$sql = $this->link->query('SELECT id,name FROM php_integration_tests WHERE id=1');
		$row = $sql->fetch_assoc();

		$this->assertEquals(1, $sql->num_rows);
		$this->assertEquals(array('id'=> 1, 'name'=> 'a'), $row);
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function insert_MultipleRowsGiven_AllRowsAreInserted() {
		$db = $this->createHelper();
		
		$db->insert(array(
			array(
				'id'=> '1',
				'name'=> 'a'
			), array(
				'id'=> '2',
				'name'=> 'b'
			)
		))->into('php_integration_tests')->exec();

		$sql = $this->link->query('SELECT id,name FROM php_integration_tests');
		$this->assertEquals(2, $sql->num_rows);
		$this->assertEquals(array('id'=> 1, 'name'=> 'a'), $sql->fetch_assoc());
		$this->assertEquals(array('id'=> 2, 'name'=> 'b'), $sql->fetch_assoc());
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function insert_RowIsInserted_ResultContainsRequiredData() {
		$db = $this->createHelper();
		// We insert this row to ensure that the next auto-id should be 2
		$db->insert(array('id'=>1,'name'=>'a'))->into('php_integration_tests')->exec();
		
		$result = $db->insert(array(
			'name'=> 'b'
		))->into('php_integration_tests')->exec();
		
		$this->assertEquals(1, $result->length(), 'One row was inserted');
		$this->assertEquals(2, $result->getLastId());
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function insert_RowContainsQuotes_RowIsInsertedCorrectly() {
		$db = $this->createHelper();
		// We insert this row to ensure that the next auto-id should be 2
		$db->insert(array('id'=>1,'name'=>'a"b\"c'."'d\'e"))->into('php_integration_tests')->exec();
		
		$row = $this->link->query('select name from php_integration_tests where id=1')->fetch_assoc();
		
		$this->assertEquals('a"b\"c'."'d\'e", $row['name']);
	}

	/**
	 * @test
	 * This test uses the insert-functionality tested earlier. If that fails,
	 * this test makes no sense
	 * @depends insert_MultipleRowsGiven_AllRowsAreInserted
	 */
	public function select_RowsAreSelected_TheyCanBeIterated() {
		$db = $this->createHelper();
		$db->insert(array(
			array(
				'id'=> '1',
				'name'=> 'a'
			), array(
				'id'=> '2',
				'name'=> 'b'
			)
		))->into('php_integration_tests')->exec();
		
		$result = $db->select('id, name')->from('php_integration_tests')->exec();
		
		$this->assertEquals(2, $result->length());
		$this->assertEquals(array('id'=> 1, 'name'=> 'a'), $result->getRow());
		$this->assertEquals(array('id'=> 2, 'name'=> 'b'), $result->getRow());
	}

	/**
	 * @test
	 * @depends insert_MultipleRowsGiven_AllRowsAreInserted
	 * @depends select_RowsAreSelected_TheyCanBeIterated
	 */
	public function delete_DeletingWithWhere_RowsAreDeleted() {
		$db = $this->createHelper();
		$db->insert(array(
			array('name'=>'a'),
			array('name'=>'b')
		))->into('php_integration_tests')->exec();
		
		$result = $db->delete()
			->from('php_integration_tests')
			->where('name="a"')->exec();
		
		$this->assertEquals(1, $result->length(), '1 row was deleted');
		$remainingRows = $db->select('id, name')->from('php_integration_tests')->exec();
		$this->assertEquals(1, $remainingRows->length(), '1 row is left');
		$this->assertEquals(array('id'=>2, 'name'=>'b'), $remainingRows->getRow());
	}

	/**
	 * @test
	 * @depends insert_MultipleRowsGiven_AllRowsAreInserted
	 * @depends select_RowsAreSelected_TheyCanBeIterated
	 */
	public function update_RowIsUpdated_NewValuesStick() {
		$db = $this->createHelper();
		
		$db->insert(array(
			array('name'=>'a'),
			array('name'=>'b')
		))->into('php_integration_tests')->exec();
		
		$result = $db->update('php_integration_tests')->set(
			array('name'=>'aa')
		)->where('name="a"')->exec();
		
		$this->assertEquals(1, $result->length(), '1 row was updated');
		
		$rows = $db->select('id, name')->from('php_integration_tests')->exec();
		$this->assertEquals(2, $rows->length(), 'Both rows are still there');
		$this->assertEquals(array('id'=>1, 'name'=>'aa'), $rows->getRow());
		$this->assertEquals(array('id'=>2, 'name'=>'b'), $rows->getRow());
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function getDefaults_TableExists_ReturnsDefaultRow() {
		$db = $this->createHelper();
		
		$row = $db
			->getDefaults('php_integration_tests')
			->exec();
		
		$this->assertEquals(array(
			'name'=> null,
			'has_default'=> 'val ue \' "'
		), $row);
	}

	/**
	 * @test
	 * @depends mysql_setup
	 * @depends select_RowsAreSelected_TheyCanBeIterated
	 */
	public function multiQuery_2QueriesGiven_databaseIsUpdated() {
		$db = $this->createHelper();

		$result = $db->multiQuery(
		  'insert into php_integration_tests (name) values ("a")'
		, $db->insert(array('name'=>'b'))->into('php_integration_tests')
		)->exec();

		$result = $db->select('*')->from('php_integration_tests')->exec();

		$this->assertEquals(2, $result->length());
	}

	/**
	 * @test
	 */
	public function multiQuery_multipleQueriesInOneString_allAreExecuted() {
		$db = $this->createHelper();

		$result = $db->multiQuery(
		  'insert into php_integration_tests (name) values ("a");'
		. 'insert into php_integration_tests (name) values ("b")'
		)->exec();

		$result = $db->select('*')->from('php_integration_tests')->exec();

		$this->assertEquals(2, $result->length());
	}

	/**
	 * @test
	 */
	public function multiQuery_lastStatementIsSelect_resultsAreReturned() {
		$db = $this->createHelper();

		$result = $db->multiQuery(
		  $db->insert(array('name'=>'a'))->into('php_integration_tests')
		, $db->insert(array('name'=>'b'))->into('php_integration_tests')
		, $db->select('*')->from('php_integration_tests')
		)->exec();

		$this->assertEquals(2, $result->length());
	}

	private function createHelper() {
		$db = new SQLHelper(array(
			'db'=> 'test',
			'user'=> 'test-user',
			'pass'=> 'test-password'
		));
		$db->connect();
		return $db;
	}
}

class TestableIntegrationQueryBuilderWithOriginalConstructor extends QueryBuilder {
	public $query;
	public function toString() {
		return $this->query;
	}
}
class TestableIntegrationQueryBuilder extends QueryBuilder {
	private $query;
	public function __construct($conn, $query) {
		parent::__construct($conn, '');
		$this->query = $query;
	}
	public function toString() {
		return $this->query;
	}
}
