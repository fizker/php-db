<?php
namespace sql\builders;

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
?>