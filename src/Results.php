<?php
namespace sql;

class Results {
	private $length, $sql, $lastId, $array;
	public function __construct($conn, $sql) {
		if(is_array($sql)) {
			$this->array = $sql;
			return;
		}
		$this->sql = $sql;
		if(is_bool($sql) && $sql) {
			$this->length = $conn->affected_rows;
			$this->lastId = $conn->insert_id;
		}
	}
	
	public function getLastId() {
		return $this->lastId;
	}

	public function getRow() {
		return $this->nextRow();
	}

	public function nextRow() {
		if($this->array) {
			$el = current($this->array);
			next($this->array);
			return $el;
		}
		return $this->sql->fetch_assoc();
	}

	public function toArray() {
		$array = array();
		while($row = $this->getRow()) {
			$array[] = $row;
		}
		reset($array);
		$this->array = $array;
		return $array;
	}
	
	public function length() {
		if($this->length === null) {
			$this->length = $this->sql->num_rows;
		}
		return $this->length;
	}
}
?>