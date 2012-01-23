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
		$link = mysql_connect('localhost', 'test-user', 'test-password');
		mysql_select_db('test');
		mysql_query('DROP TABLE IF EXISTS php_integration_tests');
		mysql_query('CREATE TABLE php_integration_tests ( 
			id int UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(200),
			has_default VARCHAR(10) DEFAULT "val",
			PRIMARY KEY(id)
		)');
		mysql_close($link);
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
		$link = mysql_connect('localhost', 'test-user', 'test-password');
		mysql_select_db('test');
		$db = new TestableIntegrationQueryBuilder('SELECT * FROM php_integration_tests');
		
		$result = $db->exec();
		
		$this->assertEquals(0, $result->length());
		mysql_close($link);
	}

	/**
	 * @test
	 * @depends mysql_setup
	 */
	public function exec_MySQL_TwoRowsInDatabase_ReturnsTwoRows() {
		$link = mysql_connect('localhost', 'test-user', 'test-password');
		mysql_select_db('test');
		mysql_query('INSERT INTO php_integration_tests (id, name) VALUES (1, "bum"), (2, "bang")');
		
		$db = new TestableIntegrationQueryBuilder('SELECT * FROM php_integration_tests');
		
		$result = $db->exec();
		
		$this->assertEquals(2, $result->length());
		mysql_close($link);
	}
	
	/**
	 * @test
	 */
	public function insert_DataIsGiven_TheRowIsInserted() {
		$db = $this->createHelper();
		
		$db->insert(array(
			'id'=> '1',
			'name'=> 'a'
		))->into('php_integration_tests')->exec();
		$sql = mysql_query('SELECT id,name FROM php_integration_tests WHERE id=1');
		$row = mysql_fetch_assoc($sql);

		$this->assertEquals(1, mysql_num_rows($sql));
		$this->assertEquals(array('id'=> 1, 'name'=> 'a'), $row);
	}

	/**
	 * @test
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
		$sql = mysql_query('SELECT id,name FROM php_integration_tests');

		$this->assertEquals(2, mysql_num_rows($sql));
		$this->assertEquals(array('id'=> 1, 'name'=> 'a'), mysql_fetch_assoc($sql));
		$this->assertEquals(array('id'=> 2, 'name'=> 'b'), mysql_fetch_assoc($sql));
	}

	/**
	 * @test
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
	 */
	public function getDefaults_TableExists_ReturnsDefaultRow() {
		$db = $this->createHelper();
		
		$row = $db
			->getDefaults('php_integration_tests')
			->exec();
		
		$this->assertEquals(array(
			'name'=> null,
			'has_default'=> 'val'
		), $row);
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
	public function __construct($query) {
		$this->query = $query;
	}
	public function toString() {
		return $this->query;
	}
}
?>