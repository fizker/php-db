<?php
namespace sql;

include(__DIR__.'/Results.php');
include(__DIR__.'/builders/QueryBuilder.php');
include(__DIR__.'/builders/InsertQueryBuilder.php');
include(__DIR__.'/builders/SelectQueryBuilder.php');
include(__DIR__.'/builders/UpdateQueryBuilder.php');
include(__DIR__.'/builders/DeleteQueryBuilder.php');

use \sql\builders\SelectBuilder;
use \sql\builders\DeleteBuilder;
use \sql\builders\UpdateBuilder;
use \sql\builders\InsertBuilder;

class SQLHelper {
	private $prefix, $db;
	
	public function __construct($credentials) {
		if(isset($credentials['db']))
			$this->setDatabase($credentials['db']);
		$this->prefix = '';
	}
	
	public function setDatabase($db) {
		$this->db = $db;
	}
	
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}
	
	public function select($what) {
		$s = new SelectBuilder($this->db, $this->prefix);
		return $s->select($what);
	}
	
	public function insert($data) {
		$i = new InsertBuilder($this->db, $this->prefix);
		return $i->insert($data);
	}
	
	public function update($table) {
		$u = new UpdateBuilder($this->db, $this->prefix);
		return $u->update($table);
	}
	
	public function delete() {
		$d = new DeleteBuilder($this->db, $this->prefix);
		return $d;
	}
	
	public function setDebug() {}
}
?>