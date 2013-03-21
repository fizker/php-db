<?php
namespace sql\tokenizers;

class StatementTokenizer {
	private $where, $type;

	public function __construct($str) {
		$this->input = trim($str);
		$str = $this->findType($this->input);

		$currentKeyword = 'TABLES';

		$keywords = new KeywordTokenizer($str, array(
			'WHERE', 'ORDER BY', 'LIMIT', 'GROUP BY'
		));

		foreach($keywords as $token) {
			if($token->isKeyword) {
				$currentKeyword = $token->value;
				continue;
			}
			switch($currentKeyword) {
				case 'WHERE':
					$this->where = $token->value;
					break;
			}
		}
	}

	private function findType() {
		$str = $this->input;
		$matches = array();
		$pattern = '/^(SELECT|INSERT|DELETE( FROM)?|UPDATE)/';
		preg_match($pattern, strtoupper($str), $matches);
		$this->type = $matches[1];
		$str = trim(substr($str, strlen($this->type)));
		return $str;
	}

	public function getWhere() {
		return array($this->where);
	}
}

class Statement {
	public function __construct($str) {}
	public function isComparison() {}
	public function isEqualityComparison() {}
	public function isInequalityComparison() {}
	public function resolveParameter($val) {}
	public function hasUnresolvedParameter() {}
}
