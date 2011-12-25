<?php
namespace sql\builders;

class InsertBuilder extends QueryBuilder {
	private $keys, $values, $table;
	
	public function insert($data) {
		if(!is_array(current($data))) {
			$data = array($data);
		}
		
		$this->keys = array_keys($data[0]);
		
		$values = array();
		foreach($data as $row) {
			$values[] = array_values($row);
		}
		$this->values = $values;
		
		return $this;
	}
	public function into($table) {
		$this->table = $table;
		return $this;
	}
	public function toString() {
		$cols = '`'.implode('`, `', $this->keys).'`';
		$vals = array();
		foreach($this->values as $row) {
			$tmpvals = array();
			foreach($row as $val) {
				$tmpvals[] = '"'.$this->escape($val).'"';
			}
			$vals[] = '('. implode(', ', $tmpvals) .')';
		}
		$table = $this->prefixTable($this->table);
		$query = 'INSERT INTO '.$table
			.' ('.$cols.') VALUES '.implode(', ', $vals);
		return $query;
	}
}
?>