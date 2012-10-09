<?php
namespace sql\builders;

class DirectQueryBuilder extends QueryBuilder {
	private $query;

	public function __construct($conn, $query = '') {
		parent::__construct($conn, '');
		$this->query($query);
	}

	public function query($query) {
		$this->query = $query;
		return $this;
	}
	public function toString() {
		return $this->query;
	}
}
?>