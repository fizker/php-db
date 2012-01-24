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
	public function getColumns_ColumnsHaveNoDefault_NullIsReturnedWhereAllowed() {
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

	/**
	 * @test
	 */
	public function getColumns_ParamsHaveSpaces_DefaultIsReturned() {
		$defaults = $this->createHelper()
			->getDefaults('any table');
		
		$rows = $defaults->getColumns('CREATE TABLE `test` (
 `a` varchar(200) DEFAULT "1 2 3",
 `b` varchar(200) DEFAULT \'4 5 6\'
) ENGINE=MyISAM');
		
		$this->assertEquals(array(
			'a'=> '1 2 3',
			'b'=> '4 5 6',
		), $rows);
	}

	/**
	 * @test
	 */
	public function getColumns_DefaultIsNotLastOnLine_DefaultIsReturned() {
		$defaults = $this->createHelper()
			->getDefaults('any table');

		$rows = $defaults->getColumns('CREATE TABLE `test` (
 `a` varchar(200) DEFAULT "1 2 3" s,
 `b` varchar(200) DEFAULT \'4 5 6\'  ,
 `c` varchar(200) DEFAULT NULL   fd,
 `d` int DEFAULT 3 5 gf 5
) ENGINE=MyISAM');

		$this->assertEquals(array(
			'a'=> '1 2 3',
			'b'=> '4 5 6',
			'c'=> null,
			'd'=> 3
		), $rows);
	}

	/**
	 * @test
	 */
	public function getColumns_DefaultHasQuotes_DefaultIsReturned() {
		$defaults = $this->createHelper()
			->getDefaults('any table');

		$rows = $defaults->getColumns('CREATE TABLE `test` (
 `a` varchar(200) DEFAULT "a\'b""",
 `b` varchar(200) DEFAULT \'a\'\'b"\',
 `c` varchar(200) DEFAULT \'"\',
 `d` varchar(200) DEFAULT "\'"
) ENGINE=MyISAM');

		$this->assertEquals(array(
			'a'=> 'a\'b"',
			'b'=> 'a\'b"',
			'c'=> '"',
			'd'=> "'"
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