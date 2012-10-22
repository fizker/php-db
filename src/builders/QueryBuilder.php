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
	public final function __toString() {
		return $this->toString();
	}

	public function exec() {
		$query = $this->toString();
		if($this->useDebug) {
			return $query;
		}

		$sql = $this->conn->query($query);

		if(!$sql) throw new \Exception($this->conn->error.".\n$query");
		return new Results($this->conn, $sql);
	}
	
	public static function escape($str) {
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
	public function prefixTable($table) {
		if($this->prefix && strpos($table, $this->prefix) !== 0) {
			$table = $this->prefix.'_'.$table;
		}
		return '`'.$this->db.'`.'.$table;
	}
	
	public static function addParams($str, $params) {
		$tokens = new ParamTokenizer($str);
		if($tokens->count() !== sizeof($params)) {
			throw new \InvalidArgumentException('Number of params does not match');
		}
		$str = $tokens->next();
		foreach($params as $param) {
			$str .= self::escape($param);
			$str .= $tokens->next();
		}
		return $str;
	}
}
?>