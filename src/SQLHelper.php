<?php
namespace sql;

require_once(__DIR__.'/Results.php');
require_once(__DIR__.'/builders/QueryBuilder.php');
require_once(__DIR__.'/builders/DirectQueryBuilder.php');
require_once(__DIR__.'/builders/InsertQueryBuilder.php');
require_once(__DIR__.'/builders/SelectQueryBuilder.php');
require_once(__DIR__.'/builders/UpdateQueryBuilder.php');
require_once(__DIR__.'/builders/DeleteQueryBuilder.php');
require_once(__DIR__.'/builders/DefaultsQueryBuilder.php');
require_once(__DIR__.'/LiteralValue.php');

use \sql\builders\DirectQueryBuilder;
use \sql\builders\SelectBuilder;
use \sql\builders\DeleteBuilder;
use \sql\builders\UpdateBuilder;
use \sql\builders\InsertBuilder;
use \sql\builders\DefaultsQueryBuilder;

class SQLHelper {
	public function __construct($options) {
		$this->options = array_merge(
		  array('prefix'=> '', 'host'=> 'localhost')
		, $options
		);
	}
	
	public function connect() {
		$options = $this->options;
		mysql_connect('localhost', $options['user'], $options['pass']);
		mysql_select_db($options['db']);
		return $this;
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
		$s = new SelectBuilder($this->options['db'], $this->options['prefix']);
		return $s->select($what);
	}
	
	public function insert($data) {
		$i = new InsertBuilder($this->options['db'], $this->options['prefix']);
		return $i->insert($data);
	}
	
	public function update($table) {
		$u = new UpdateBuilder($this->options['db'], $this->options['prefix']);
		return $u->update($table);
	}
	
	public function delete() {
		$d = new DeleteBuilder($this->options['db'], $this->options['prefix']);
		return $d;
	}
	
	public function query($query) {
		$q = new DirectQueryBuilder($this->options['db']);
		return $q->query($query);
	}
	
	public function getDefaults($table) {
		$b = new DefaultsQueryBuilder($this->options['db'], $this->options['prefix']);
		$b->forTable($table);
		return $b;
	}
}
