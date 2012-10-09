<?php
namespace sql\builders;

class MultiQueryBuilder extends QueryBuilder {
	private $queries;

	public function __construct($conn) {
		parent::__construct($conn, '');
		$this->queries = array();
	}

	public function query($query) {
		if(is_array($query)) {
			foreach($query as $q) {
				$this->query($q);
			}
			return $this;
		}
		if($query instanceof QueryBuilder) {
			$query = $query->toString();
		}
		$this->queries[] = $query;
		return $this;
	}

	public function toString() {
		return implode($this->queries, ';');
	}
}
