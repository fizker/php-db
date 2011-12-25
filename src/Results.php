<?php
namespace sql;

class Results {
	private $length, $sql;
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function length() {
		if($this->length === null) {
			$this->length = mysql_num_rows($this->sql);
		}
		return $this->length;
	}
}
?>