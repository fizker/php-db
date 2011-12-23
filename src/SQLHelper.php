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

class SelectBuilder extends QueryBuilder {
	private $what, $from, $where, $order;
	
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
		
		$from = array();
		foreach($f as $f) {
			$f = $this->prefixTable($f);
			$from[] = $f;
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
	public function toString() {
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
	public function toString() {
		$cols = array();
		$vals = array();
		foreach($this->data as $col=>$val) {
			$cols[] = '`'.$col.'`';
			$vals[] = '"'.$this->escape($val).'"';
		}
		$table = $this->prefixTable($this->table);
		$query = 'INSERT INTO '.$table
			.' ('.implode(', ', $cols).') VALUES ('.implode(', ', $vals).')';
		return $query;
	}
}

class UpdateBuilder extends QueryBuilder {
	private $table, $data, $where;
	public function update($table) {
		$this->table = $table;
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
	public function toString() {
		$query = 'UPDATE '.$this->prefixTable($this->table).' SET ';
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

class DeleteBuilder extends QueryBuilder {
	private $table, $where;
	
	public function from($table) {
		$this->table = $table;
		return $this;
	}
	
	public function where($where) {
		$this->where = $where;
		return $this;
	}
	
	public function toString() {
		if(!$this->table) {
			throw new \InvalidArgumentException('Table should be given');
		}
		
		$query = 'DELETE FROM '.$this->prefixTable($this->table);
		
		if($this->where) {
			$query .= ' WHERE '.$this->where;
		}
		
		return $query;
	}
}

abstract class QueryBuilder {
	private $db, $prefix;
	public function __construct($db, $prefix = '') {
		$this->db = $db;
		$this->prefix = $prefix;
	}
	
	public abstract function toString();
	public final function exec() {
		return $this->toString();
	}
	
	protected function escape($str) {
		return str_replace(
			array('\"',		'"'), 
			array('\\\"',	'\"'), 
			$str);
	}
	protected function prefixTable($table) {
		if($this->prefix && strpos($table, $this->prefix) !== 0) {
			$table = $this->prefix.'_'.$table;
		}
		return $this->db.'.'.$table;
	}
}
?>