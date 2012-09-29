<?php
namespace sql\builders;

class SelectBuilder extends QueryBuilder {
	private $what, $from, $where, $order, $group, $limit;

	public function select($w) {
		$what = array();
		if(is_array($w)) {
			foreach($w as $k=>$v) {
				if(is_int($k)) {
					$what[] = $v;
				} else {
					$what[] = $v.' AS '.$k;
				}
			}
			$what = implode(', ', $what);
		} else {
			$what = $w;
		}
		$this->what = $what;

		return $this;
	}

	public function from($f) {
		if(!is_array($f)) {
			$f = array($f);
		}

		$from = array();
		foreach($f as $f) {
			$f = $this->prefixTable($f);
			$from[] = $f;
		}
		$from = implode(', ', $from);

		$this->from = $from;
		return $this;
	}

	public function where($where, $param = false) {
		if($param != false) {
			$params = array_slice(func_get_args(), 1);
			$where = $this->addParams($where, $params);
		}
		$this->where = $where;
		return $this;
	}

	public function order($order) {
		$this->order = $order;
		return $this;
	}

	public function group($group) {
		$group = implode(func_get_args(), '`, `');
		$this->group = "`$group`";
		return $this;
	}

	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	public function toString() {
		$query = "SELECT $this->what FROM $this->from";

		if($this->where)
			$query .= " WHERE $this->where";

		if($this->group)
			$query .= ' GROUP BY '.$this->group;

		if($this->order)
			$query .= ' ORDER BY '.$this->order;

		if($this->limit)
			$query .= ' LIMIT '.$this->limit;

		return $query;
	}
}
