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
		$this->keywords = $keywords = array_map(function($w) {
			return new Keyword($w);
		}, $keywords);

		$this->results = array();
		$words = explode(' ', $str);
		$nextWord = '';
		foreach($words as $word) {
			$nextWord .= ' ' . $word;

			foreach($keywords as $keyword) {
				if($keyword->matches($word)) {
					$nextWord = substr($nextWord, 0, -strlen($keyword->keyword));
					if($nextWord) {
						$this->results[] = new Token(trim($nextWord), false);
						$nextWord = '';
					}
					$this->results[] = new Token($keyword->keyword, true);
				}
			}
		}
		if($nextWord) {
			$this->results[] = new Token(trim($nextWord), false);
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
	private $words, $partialWords = array();
	public $keyword;
	public function __construct($keyword) {
		$this->words = sizeof(explode(' ', $keyword));
		$this->keyword = $keyword;
	}

	public function matches($word) {
		// Incrementing the list of partial words
		$p = array();
		foreach($this->partialWords as $partial) {
			$p[] = array(
				$partial[0] + 1,
				$partial[1] . ' ' . $word
			);
		}
		$p[] = array(1, $word);

		// The word to match against
		$word = $p[0];

		// Removing the top if it has exceeded the keyword length
		if($word[0] > $this->words) {
			array_shift($p);
			$word = $p[0];
		}
		$this->partialWords = $p;

		// Getting the actual string
		$word = $word[1];

		return $word === $this->keyword;
	}
}

class Token {
	public $value, $isKeyword;
	public function __construct($value, $isKeyword) {
		$this->value = $value;
		$this->isKeyword = $isKeyword;
	}
}
