<?php
namespace sql\tokenizers;

/*
this should iterate through $str, splitting along any entry in $keywords unless
they are within quotes, and return either the content or the keyword on ::next(),
as appropriate.
*/
class KeywordTokenizer {
	private $input, $keywords;
	public function __construct($str, $keywords) {
		$this->input = $str;
		$this->keywords = $keywords;

		$this->results = array();
		$words = explode(' ', $str);
		$nextWord = '';
		foreach($words as $word) {
			if(in_array($word, $keywords)) {
				if($nextWord) {
					$this->results[] = new Keyword(trim($nextWord), false);
					$nextWord = '';
				}
				$this->results[] = new Keyword($word, true);
				continue;
			}
			$nextWord .= ' ' . $word;
		}
		if($nextWord) {
			$this->results[] = new Keyword(trim($nextWord), false);
		}
	}

	public function next() {
		$c = current($this->results);
		if($c === false) {
			return null;
		}
		next($this->results);
		return $c;
	}
}

class Keyword {
	public $value, $isKeyword;
	public function __construct($value, $isKeyword) {
		$this->value = $value;
		$this->isKeyword = $isKeyword;
	}
}
