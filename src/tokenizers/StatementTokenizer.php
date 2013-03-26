<?php
namespace sql\tokenizers;

class StatementTokenizer {
	private $where, $type;
	private $tokens;

	public function __construct($str) {
		$this->input = $str = trim($str);

		$keywords = new KeywordTokenizer($str, array(
			'SELECT', 'INSERT', 'DELETE FROM', 'UPDATE',
			'WHERE', 'ORDER BY', 'LIMIT', 'GROUP BY'
		));

		$this->tokens = array();
		foreach($keywords as $token) {
			if($token->isKeyword) {
				$currentKeyword = $token->value;
				$this->tokens[] = new KeywordStatement($token->value);
				continue;
			}
			switch($currentKeyword) {
				case 'WHERE':
					$this->setWhere($token->value);
					break;
				default:
					$this->tokens[] = new Statement($token->value);
					break;
			}
		}
	}

	private function setWhere($str) {
		$tokens = new KeywordTokenizer($str, array('AND', 'OR'));
		foreach($tokens as $token) {
			if($token->isKeyword) {
				$this->tokens[] = new KeywordStatement($token->value);
				continue;
			}
			$this->tokens[] = new WhereStatement($token->value);
		}
	}

	public function getWhere() {
		return $this->where;
	}

	public function __toString() {
		$str = '';
		while($token = $this->next()) {
			$str .= $token->value.' ';
		}
		return trim($str);
	}

	public function resolveParameters($params) {
		$str = '';
		while($token = $this->next()) {
			$params = $token->resolveParameters($params);
			$str .= $token->value.' ';
		}
		return trim($str);
	}

	private function next() {
		$c = current($this->tokens);
		if($c === false) {
			return null;
		}
		next($this->tokens);
		return $c;
	}
}

class KeywordStatement {
	public $value;
	public function __construct($str) {
		$this->value = $str;
	}

	public function __toString() {
		return $this->value;
	}

	public function resolveParameters($params) {
		return $params;
	}
}

class Statement extends KeywordStatement {
	protected $params;
	public function __construct($str) {
		$this->value = $str;
		$this->params = new ParamTokenizer($str);
	}

	protected function addParameter($current, $val) {
		return $current . \sql\builders\QueryBuilder::escape($val) . $this->params->next();
	}

	public function resolveParameters($params) {
		$return = $this->params->next();
		$l = $this->params->count();
		for($i = 0; $i < $l; $i++) {
			$return = $this->addParameter($return, array_shift($params));
		}
		$this->value = $return;
		return $params;
	}
}

class WhereStatement extends Statement {
	public function isComparison() {}

	public function isEqualityComparison() {
		return !$this->isInequalityComparison();
	}
	public function isInequalityComparison() {
		return strpos($this->value, '!=') !== false || strpos($this->value, '<>') !== false;
	}

	protected function addParameter($current, $val) {
		if($val === null) {
			$comparator = array('!=', '<>');
			$suffix = ' IS NOT NULL';
			if($this->isEqualityComparison()) {
				$comparator = '=';
				$suffix = ' IS NULL';
			}
			return str_replace($comparator, '', $current.$this->params->next()) . $suffix;
		}
		return parent::addParameter($current, $val);
	}
}
