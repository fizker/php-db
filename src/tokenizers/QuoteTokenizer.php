<?php 
namespace sql\tokenizers;

class QuoteTokenizer {
	private $str, $index, $chars;
	public function __construct($str, $chars = array( array('"'), array("'") )) {
		$this->str = $str;
		$this->index = 0;
		$this->chars = array_map(function($ch) {
			if(sizeof($ch) === 1) {
				$ch[1] = $ch[0];
			}
			return $ch;
		}, $chars);
	}
	
	public function next() {
		$str = $this->str;
		$index = $this->index;
		$next = array_map(function($char) use ($str, $index) {
			return array(
				'char'=> $char,
				'pos'=> strpos($str, $char[0], $index)
			);
		}, $this->chars);
		$next = array_reduce($next, function($a, $b) {
			$ap = false;
			$bp = false;
			if($a !== null) {
				$ap = $a['pos'];
			}
			if($ap === false) {
				$ap = PHP_INT_MAX;
			}
			if($b !== null) {
				$bp = $b['pos'];
			}
			if($bp === false) {
				$bp = PHP_INT_MAX;
			}
			
			return $ap < $bp
				? $a
				: $b;
		});
		if($next !== null) {
			$index = $next['pos'];
			if($index === false) {
				return null;
			}
			$c = $next['char'][1];
			$end = $index-1;
			do {
				$end = strpos($this->str, $c, $end+2);
			} while(strlen($this->str)>$end+2 && $this->str[$end] === $c && $this->str[$end+1] === $c);
			
			$this->index = $end + 1;
			
			return new Quote(substr($this->str, $index+1, $end - $index - 1), $next['char'][0], $index);
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