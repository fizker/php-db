<?php
namespace sql\builders;

use \sql\Results;

abstract class QueryBuilder {
	private $db, $prefix, $useDebug;
	public function __construct($db, $prefix = '', $useDebug = false) {
		$this->useDebug = $useDebug;
		$this->db = $db;
		$this->prefix = $prefix;
	}
	
	public abstract function toString();
	
	public final function exec() {
		$query = $this->toString();
		if($this->useDebug) {
			return $query;
		}
		
		$sql = mysql_query($query);
		if(!$sql) throw new \Exception(mysql_error());
		return new Results($sql);
	}
	
	protected function escape($str) {
		return str_replace(
			array('\"',		'"'), 
			array('\\\"',	'\"'), 
			$str);
	}
	protected function prefixTable($table) {
		if($this->prefix && strpos($table, $this->prefix) !== 0) {
			$table = $this->prefix.'_'.$table;
		}
		return $this->db.'.'.$table;
	}
}
?>