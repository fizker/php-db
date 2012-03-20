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
	protected $prefix, $db;
	
	public function __construct($credentials) {
		if(isset($credentials['db']))
			$this->setDatabase($credentials['db']);
		$this->credentials = $credentials;
		$this->prefix = '';
	}
	
	public function connect() {
		$creds = $this->credentials;
		mysql_connect('localhost', $creds['user'], $creds['pass']);
		mysql_select_db($this->db);
		return $this;
	}
	
	public function setDatabase($db) {
		$this->db = $db;
		return $this;
	}
	
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		return $this;
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
	
	public function query($query) {
		$q = new DirectQueryBuilder($this->db);
		return $q->query($query);
	}
	
	public function getDefaults($table) {
		$b = new DefaultsQueryBuilder($this->db, $this->prefix);
		$b->forTable($table);
		return $b;
	}
}
?>