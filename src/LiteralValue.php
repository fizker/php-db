<?php
namespace sql;

class LiteralValue {
	private $val;
	public function __construct($val) {
		$this->val = $val;
	}

	public function toString() {
		return $this->val;
	}
	public function __toString() {
		return $this->toString();
	}
}
