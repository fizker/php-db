<?php
namespace sql\builders;

class DefaultsQueryBuilder extends QueryBuilder {
	private $table;
	
	private function parseColumn($name, $col, &$output) {
		$matches = array();
		$match = preg_match('/DEFAULT (NULL|\d+|".+"|\'.+\')/i', $col, $matches);
		if($match) {
			$match = $matches[1];
			if($match === 'NULL') {
				$match = null;
			} else if($match[0] === '"' || $match[0] === "'") {
				$quote = $match[0];
				$match = substr($match, 1, -1);
				$match = str_replace($quote.$quote, $quote, $match);
			} else {
				$match = (int)$match;
			}
			$output[$name] = $match;
		} else if(strpos($col, 'NOT NULL') === false) {
			$output[$name] = null;
		}
	}
	
	public function splitIntoColumns($cols) {
		$return = array();
		$tokenizer = new \sql\tokenizers\ParamTokenizer(
			$cols
			, ','
			, array( array('"'), array("'"), array('(',')') ));
		
		while($col = $tokenizer->next()) {
			$return[] = trim($col);
		}
		return $return;
	}

	public function getColumns($input) {
		$first = strpos($input, '(')+1;
		$last = strrpos($input, ')');
		$cols = substr($input, $first, $last - $first);
		$cols = $this->splitIntoColumns($cols);
		
		$defaults = array();
		foreach($cols as $col) {
			$col = trim($col);
			if($col[0] !== '`') {
				continue;
			}
			$i = strpos($col, '`', 1);
			$name = substr($col, 1, $i-1);
			$this->parseColumn($name, $col, $defaults);
		}
		return $defaults;
	}
	
	public function forTable($table) {
		$this->table = $table;
		return $this;
	}
	
	public function toString() {}
	
	public function exec() {
		$qb = new DirectQueryBuilder($this->db);
		
		$row = $qb
			->query('SHOW CREATE TABLE '
				.$this->prefixTable($this->table))
			->exec()
			->getRow();

		return $this->getColumns($row['Create Table']);
	}
}
?>