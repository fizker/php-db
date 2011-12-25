<?php
namespace sql;

class Results {
	private $length, $sql, $lastId;
	public function __construct($sql) {
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
		return mysql_fetch_assoc($this->sql);
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