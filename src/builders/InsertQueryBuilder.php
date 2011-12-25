<?php
namespace sql\builders;

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
?>