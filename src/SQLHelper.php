<?php
namespace sql;

class SQLHelper {
	public function __construct($credentials) {}
	
	public function select($what) {
		return new SelectBuilder($what);
	}
	
	public function setDebug() {}
}

class SelectBuilder {
	private $what, $from, $where;
	
	public function __construct($w) {
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
	}
	public function from($f) {
		if(is_array($f)) {
			$from = implode(', db.', $f);
		} else {
			$from = $f;
		}
		$this->from = $from;
		return $this;
	}
	public function where($where) {
		$this->where = $where;
		return $this;
	}
	public function exec() {
		$query = "SELECT $this->what FROM db.$this->from";
		if($this->where)
			$query .= " WHERE $this->where";
		
		return $query;
	}
}
?>