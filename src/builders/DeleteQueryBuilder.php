<?php
namespace sql\builders;

class DeleteBuilder extends QueryBuilder {
	private $table, $where;
	
	public function from($table) {
		$this->table = $table;
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
?>