<?php
namespace sql\builders;

class DeleteBuilder extends QueryBuilder {
	private $table, $where;

	public function from($table) {
		$this->table = $table;
		return $this;
	}

	public function where($where, $param = false) {
		$where = 'WHERE '.$where;
		if(func_num_args() > 1) {
			$params = array_slice(func_get_args(), 1);
			$where = $this->addParams($where, $params);
		}
		$this->where = $where;
		return $this;
	}

	public function toString() {
		if(!$this->table) {
			throw new \InvalidArgumentException('Table should be given');
		}
		
		$query = 'DELETE FROM '.$this->prefixTable($this->table);

		if($this->where) {
			$query .= ' '.$this->where;
		}

		return $query;
	}
}
