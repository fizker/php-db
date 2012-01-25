<?php
namespace sql\builders;

require_once(__DIR__.'/../tokenizers/ParamTokenizer.php');

use \sql\Results;
use \sql\tokenizers\ParamTokenizer;

abstract class QueryBuilder {
	protected $db, $prefix, $useDebug;
	public function __construct($db, $prefix = '', $useDebug = false) {
		$this->useDebug = $useDebug;
		$this->db = $db;
		$this->prefix = $prefix;
	}
	
	public abstract function toString();
	
	public function exec() {
		$query = $this->toString();
		if($this->useDebug) {
			return $query;
		}
		
		$sql = mysql_query($query);
		if(!$sql) throw new \Exception(mysql_error());
		return new Results($sql);
	}
	
	public function escape($str) {
		if($str === null) {
			return 'NULL';
		}
		return '"'.str_replace(
			array('\"',		'"'), 
			array('\\\"',	'\"'), 
			$str).'"';
	}
	protected function prefixTable($table) {
		if($this->prefix && strpos($table, $this->prefix) !== 0) {
			$table = $this->prefix.'_'.$table;
		}
		return $this->db.'.'.$table;
	}
	
	protected function addParams($str, $params) {
		$tokens = new ParamTokenizer($str);
		if($tokens->count() !== sizeof($params)) {
			throw new \InvalidArgumentException('Number of params does not match');
		}
		$str = $tokens->next();
		foreach($params as $param) {
			$str .= $this->escape($param);
			$str .= $tokens->next();
		}
		return $str;
	}
}
?>