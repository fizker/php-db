<?php
namespace sql;

class Results {
	private $length, $sql, $lastId, $array;
	public function __construct($sql) {
		if(is_array($sql)) {
			$this->array = $sql;
			return;
		}
		$this->sql = $sql;
		if(is_bool($sql) && $sql) {
			$this->length = mysql_affected_rows();
			$sql = mysql_query('SELECT LAST_INSERT_ID()');
			$id = mysql_fetch_row($sql);
			$this->lastId = $id[0];
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
		return mysql_fetch_assoc($this->sql);
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
			if(is_bool($this->sql)) var_dump($this->sql);
			$this->length = mysql_num_rows($this->sql);
		}
		return $this->length;
	}
}
?>