<?php

include_once(__DIR__.'/../../src/SQLHelper.php');

use \sql\SQLHelper;

class SQLHelperTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function query_QueryIsGiven_QueryIsUsedAsIs() {
		$sqlhelper = $this->createHelper();
		
		$result = $sqlhelper
			->query('a b c')
			->toString();
		
		$this->assertEquals('a b c', $result);
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
?>