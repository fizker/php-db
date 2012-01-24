<?php

include_once(__DIR__.'/../../../src/SQLHelper.php');

use \sql\SQLHelper;
use \sql\DefaultsQueryBuilder;

/**
 * NOTE: The tests does not verify the actual db-connection.
 * The built-in mysql-calls cannot be stubbed, so this is not
 * possible as unit-tests. It will be done later on as integration-tests
 */
class DefaultsQueryBuilderTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function getColumns_ColumnsHaveDefault_AllColumnsReturned() {
		$defaults = $this->createHelper()
			->getDefaults('any table');
		
		$rows = $defaults->getColumns('CREATE TABLE `test` (
 `a` int(11) DEFAULT NULL,
 `b` int DEFAULT 2,
 `c` varchar(200) DEFAULT "d",
 `d` varchar(200) DEFAULT \'c\',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1');
		
		$this->assertEquals(array(
			'a'=> null,
			'b'=> 2,
			'c'=> 'd',
			'd'=> 'c'
		), $rows);
	}

	/**
	 * @test
	 */
	public function getColumns_ColumnsHaveNoDefault_WatWat() {
		$defaults = $this->createHelper()
			->getDefaults('any table');
		
		$rows = $defaults->getColumns('CREATE TABLE `test` (
 `a` int(11),
 `b` int NOT NULL,
 `c` varchar(200),
 `d` varchar(200) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1');
		
		$this->assertEquals(array(
			'a'=> null,
			'c'=> null,
		), $rows);
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