<?php
namespace sql\builders;

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
?>