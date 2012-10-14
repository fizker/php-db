<?php
namespace sql\builders;

class JoinBuilder {
	private $clause, $alias;
	public function __construct($builder, $table) {
		$this->builder = $builder;
		if($table instanceof SelectBuilder) {
			$table = "($table)";
		} else {
			$table = $builder->prefixTable($table);
		}
		$this->table = $table;
	}

	public function on($clause) {
		$this->clause = $clause;
		return $this;
	}

	public function __call($method, $args) {
		switch($method) {
			case 'as':
				return $this->_as($args);
		}
		throw new \Exception('No such method');
	}
	public function _as($args) {
		$this->alias = $args[0];
		return $this;
	}

	public function done() {
		return $this->builder->join($this);
	}

	public function toString() {
		$table = $this->table;
		if($this->alias) {
			$table .= " AS $this->alias";
		}

		if($this->clause) {
			$s = "INNER JOIN $table ON $this->clause";
		} else {
			$s = "CROSS JOIN $table";
		}

		return $s;
	}
	public function __toString() {
		return $this->toString();
	}
}
