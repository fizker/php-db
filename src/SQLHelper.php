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
	
	public function insert($data) {
		$i = new InsertBuilder();
		return $i->insert($data);
	}
	
	public function update($table) {
		$u = new UpdateBuilder();
		return $u->update($table);
	}
	
	public function setDebug() {}
}

class SelectBuilder extends QueryBuilder {
	private $db, $prefix;
	private $what, $from, $where, $order;
	
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
	public function order($order) {
		$this->order = $order;
		return $this;
	}
	public function exec() {
		$query = "SELECT $this->what FROM $this->from";
		if($this->where)
			$query .= " WHERE $this->where";
		if($this->order)
			$query .= ' ORDER BY '.$this->order;
		
		return $query;
	}
}

class InsertBuilder extends QueryBuilder {
	private $data, $table;
	
	public function insert($data) {
		$this->data = $data;
		return $this;
	}
	public function into($table) {
		$this->table = $table;
		return $this;
	}
	public function exec() {
		$cols = array();
		$vals = array();
		foreach($this->data as $col=>$val) {
			$cols[] = '`'.$col.'`';
			$vals[] = '"'.$this->escape($val).'"';
		}
		$query = 'INSERT INTO db.'.$this->table
			.' ('.implode(', ', $cols).') VALUES ('.implode(', ', $vals).')';
		return $query;
	}
}

class UpdateBuilder extends QueryBuilder {
	private $data, $where;
	public function update() {
		return $this;
	}
	public function set($data) {
		$this->data = $data;
		return $this;
	}
	public function where($where) {
		$this->where = $where;
		return $this;
	}
	public function exec() {
		$query = 'UPDATE db.table SET ';
		foreach($this->data as $col=>$val) {
			$query .= '`'.$col.'`="'.$this->escape($val).'", ';
		}
		$query = substr($query, 0, -2);

		if($this->where) {
			$query .= ' WHERE '.$this->where;
		}
		
		return $query;
	}
}

abstract class QueryBuilder {
	public abstract function exec();
	
	protected function escape($str) {
		return str_replace(
			array('\"',		'"'), 
			array('\\\"',	'\"'), 
			$str);
	}
}
?>