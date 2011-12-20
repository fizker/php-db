<?php
namespace sql;

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
	
	public function setDebug() {}
}

class SelectBuilder {
	private $db, $prefix;
	private $what, $from, $where;
	
	public function __construct($db, $prefix = '') {
		$this->db = $db;
		$this->prefix = $prefix;
	}
	
	public function select($w) {
		$what = array();
		if(is_array($w)) {
			foreach($w as $k=>$v) {
				if(is_int($k)) {
					$what[] = $v;
				} else {
					$what[] = $v.' AS '.$k;
				}
			}
			$what = implode(', ', $what);
		} else {
			$what = $w;
		}
		$this->what = $what;
		
		return $this;
	}
	public function from($f) {
		if(!is_array($f)) {
			$f = array($f);
		}
		
		$prefix = $this->prefix;
		$db = $this->db;
		
		$from = array();
		foreach($f as $f) {
			if($prefix && strpos($prefix, $f) !== 0) {
				$f = $prefix.'_'.$f;
			}
			$from[] = $db.'.'.$f;
		}
		$from = implode(', ', $from);
		
		$this->from = $from;
		return $this;
	}
	public function where($where) {
		$this->where = $where;
		return $this;
	}
	public function exec() {
		$query = "SELECT $this->what FROM $this->from";
		if($this->where)
			$query .= " WHERE $this->where";
		
		return $query;
	}
}
?>