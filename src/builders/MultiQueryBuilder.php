<?php
namespace sql\builders;

use \sql\Results;

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
		if(is_string($query)) {
			$query = new DirectQueryBuilder($this->conn, $query);
		}
		$this->queries[] = $query;
		return $this;
	}

	public function exec() {
		$s = $this->toString();
		if($this->useDebug) {
			return $s;
		}

		$sql = $this->conn->multi_query($s);
		if(!$sql) throw new \Exception($this->conn->error);

		$result = false;
		do {
			$r = $this->conn->store_result();
			if($r) {
				$result = new Results($this->conn, $r);
				$result->toArray();
				$r->close();
			} else {
				if($this->conn->error) {
					throw new \Exception($this->conn->error);
				}
			}
		} while(@$this->conn->next_result());
		if(!$result) {
			$result = new Results($this->conn, true);
		}
		return $result;
	}

	public function toString() {
		return implode($this->queries, ';');
	}
}
