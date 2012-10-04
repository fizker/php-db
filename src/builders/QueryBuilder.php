<?php
namespace sql\builders;

require_once(__DIR__.'/../tokenizers/ParamTokenizer.php');

use \sql\Results;
use \sql\tokenizers\ParamTokenizer;

abstract class QueryBuilder {
	protected $conn, $db, $prefix, $useDebug;
	public function __construct($conn, $db, $prefix = '', $useDebug = false) {
		$this->conn = $conn;
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

		$sql = $this->conn->query($query);

		if(!$sql) throw new \Exception(mysql_error());
		return new Results($this->conn, $sql);
	}
	
	public function escape($str) {
		if($str === null) {
			return 'NULL';
		}
		if(is_object($str)) {
			return $str->toString();
		}
		return '"'.str_replace(
			array('\\',		'"'), 
			array('\\\\',	'""'), 
			$str).'"';
	}
	protected function prefixTable($table) {
		if($this->prefix && strpos($table, $this->prefix) !== 0) {
			$table = $this->prefix.'_'.$table;
		}
		return '`'.$this->db.'`.'.$table;
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