<?php
namespace sql;

require_once(__DIR__.'/Results.php');
require_once(__DIR__.'/builders/QueryBuilder.php');
require_once(__DIR__.'/builders/DirectQueryBuilder.php');
require_once(__DIR__.'/builders/MultiQueryBuilder.php');
require_once(__DIR__.'/builders/InsertBuilder.php');
require_once(__DIR__.'/builders/SelectBuilder.php');
require_once(__DIR__.'/builders/UpdateQueryBuilder.php');
require_once(__DIR__.'/builders/DeleteQueryBuilder.php');
require_once(__DIR__.'/builders/DefaultsQueryBuilder.php');
require_once(__DIR__.'/LiteralValue.php');

use \sql\builders\DirectQueryBuilder;
use \sql\builders\MultiQueryBuilder;
use \sql\builders\SelectBuilder;
use \sql\builders\DeleteBuilder;
use \sql\builders\UpdateBuilder;
use \sql\builders\InsertBuilder;
use \sql\builders\DefaultsQueryBuilder;

class SQLHelper {
	private $conn;

	public function __construct($options) {
		$this->options = array_merge(
		  array('prefix'=> '', 'host'=> 'localhost')
		, $options
		);
	}
	
	public function connect() {
		$options = $this->options;
		$this->conn = new \mysqli(
		  'localhost'
		, $options['user']
		, $options['pass']
		, $options['db']
		);
		return $this;
	}

	public function error() {
		return $this->conn->error;
	}

	public function setDatabase($db) {
		$this->options['db'] = $db;
		return $this;
	}

	public function setHost($host) {
		$this->options['host'] = $host;
		return $this;
	}

	public function setPrefix($prefix) {
		$this->options['prefix'] = $prefix;
		return $this;
	}
	
	public function select($what) {
		$s = new SelectBuilder($this->conn, $this->options['db'], $this->options['prefix']);
		return $s->select($what);
	}
	
	public function insert($data) {
		$i = new InsertBuilder($this->conn, $this->options['db'], $this->options['prefix']);
		return $i->insert($data);
	}
	
	public function update($table) {
		$u = new UpdateBuilder($this->conn, $this->options['db'], $this->options['prefix']);
		return $u->update($table);
	}
	
	public function delete() {
		$d = new DeleteBuilder($this->conn, $this->options['db'], $this->options['prefix']);
		return $d;
	}
	
	public function query($query) {
		$q = new DirectQueryBuilder($this->conn);
		return $q->query($query);
	}

	public function multiQuery($query) {
		$mq = new MultiQueryBuilder($this->conn);
		return $mq->query(func_get_args());
	}

	public function getDefaults($table) {
		$b = new DefaultsQueryBuilder($this->conn, $this->options['db'], $this->options['prefix']);
		$b->forTable($table);
		return $b;
	}
}
