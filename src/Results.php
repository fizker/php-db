<?php
namespace sql;

class Results implements \Iterator {
	private $length, $sql, $lastId, $array;
	public function __construct($conn, $sql = null) {
		if(is_array($conn)) {
			$sql = $conn;
			$conn = null;
		}
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
		$l = 0;
		while($row = $this->getRow()) {
			$array[] = $row;
			$l++;
		}
		reset($array);
		$this->array = $array;
		$this->length = $l;
		return $array;
	}

	public function length() {
		if($this->length === null) {
			$this->length = $this->sql->num_rows;
		}
		return $this->length;
	}


	// Iterator methods

	public function key() {
		if($this->array) {
			return key($this->array);
		}
	}
	public function current() {
		if($this->array) {
			return current($this->array);
		}
	}

	public function valid() {
		if($this->array) {
			return $this->key() !== null;
		}
	}

	public function next() {
		if($this->array) {
			next($this->array);
		}
	}
	public function rewind() {
		if(!$this->array) {
			$this->toArray();
		}
		reset($this->array);
	}
}
?>