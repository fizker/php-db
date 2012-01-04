<?php
namespace sql\builders;

class DirectQueryBuilder extends QueryBuilder {
	private $query;
	
	public function query($query) {
		$this->query = $query;
		return $this;
	}
	public function toString() {
		return $this->query;
	}
}
?>