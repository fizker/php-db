<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\builders\DirectQueryBuilder;
use \sql\builders\MultiQueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class SQLHelperMultiTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function multiQuery_singleQueryGiven_queryReturned() {
		$db = $this->createHelper();

		$result = $db->multiQuery('select 1')->toString();

		$this->assertEquals('select 1', $result);
	}

	/**
	 * @test
	 */
	public function multiQuery_singleBuilderGiven_queryReturned() {
		$db = $this->createHelper();
		$q = new DirectQueryBuilder(null, 'select 1');

		$result = $db->multiQuery($q)->toString();

		$this->assertEquals('select 1', $result);
	}

	/**
	 * @test
	 */
	public function multiQuery_multipleBuildersGiven_queryIsConcatenated() {
		$db = $this->createHelper();
		$q1 = new DirectQueryBuilder(null, 'select 1');
		$q2 = new DirectQueryBuilder(null, 'select 2');

		$result = $db->multiQuery($q1)->query($q2)->toString();

		$this->assertEquals('select 1;select 2', $result);
	}

	/**
	 * @test
	 */
	public function multiQuery_multipleParams_queryIsConcatenated() {
		$db = $this->createHelper();
		$q1 = new DirectQueryBuilder(null, 'select 1');
		$q2 = new DirectQueryBuilder(null, 'select 2');

		$result = $db->multiQuery($q1, $q2)->toString();

		$this->assertEquals('select 1;select 2', $result);
	}

	/**
	 * @test
	 */
	public function multiQuery_arrayAsParam_queryIsConcatenated() {
		$db = $this->createHelper();
		$q1 = new DirectQueryBuilder(null, 'select 1');
		$q2 = new DirectQueryBuilder(null, 'select 2');

		$result = $db->multiQuery(array($q1, $q2))->toString();

		$this->assertEquals('select 1;select 2', $result);
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
