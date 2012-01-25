<?php 
namespace sql\tokenizers;

class QuoteTokenizer {
	private $str, $index;
	public function __construct($str) {
		$this->str = $str;
		$this->index = 0;
	/*
		$d = strpos($str, '"', $start);
		$s = strpos($str, "'", $start);
	*/
	}
	
	public function next() {
		$d = strpos($this->str, '"', $this->index);
		$s = strpos($this->str, "'", $this->index);

		$index = $s;
		$c = "'";
		if($d !== false) {
			if($s === false || $d < $s) {
				$c = '"';
				$index = $d;
			}
		}
		if($index !== false) {
			$end = $index-1;
			do {
				$end = strpos($this->str, $c, $end+2);
			} while(strlen($this->str)>$end+2 && $this->str[$end] === $c && $this->str[$end+1] === $c);
			
			$this->index = $end + 1;
			
			return new Quote(substr($this->str, $index+1, $end - $index - 1), $c, $index);
		}
		return null;
	}
	
	public function reset() {
		$this->index = 0;
	}
}

class Quote {
	public $char, $start, $end, $quote;
	
	public function __construct($quote, $char, $start) {
		$this->quote = $quote;
		$this->char = $char;
		$this->start = $start;
		$this->end = $start + strlen($quote) + 2;
	}
}
?>