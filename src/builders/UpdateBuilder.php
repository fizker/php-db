<?php
namespace sql\builders;

class UpdateBuilder extends QueryBuilder {
	private $table, $data, $where;
	public function update($table) {
		$this->table = $table;
		return $this;
	}
	public function set($data, $param = false) {
		if(is_string($data) && $param != false) {
			$params = array_slice(func_get_args(), 1);
			$data = $this->addParams($data, $params);
		}
		$this->data = $data;
		return $this;
	}
	public function where($where, $param = false) {
		if($param != false) {
			$params = array_slice(func_get_args(), 1);
			$where = $this->addParams($where, $params);
		}
		$this->where = $where;
		return $this;
	}
	public function toString() {
		$query = 'UPDATE '.$this->prefixTable($this->table).' SET ';
		if(is_array($this->data)) {
			foreach($this->data as $col=>$val) {
				$query .= '`'.$col.'`='.$this->escape($val).', ';
			}
			$query = substr($query, 0, -2);
		} else {
			$query .= $this->data;
		}

		if($this->where) {
			$query .= ' WHERE '.$this->where;
		}

		return $query;
	}
}
