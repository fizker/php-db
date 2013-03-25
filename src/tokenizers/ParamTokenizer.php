<?php
namespace sql\tokenizers;

require_once(__DIR__.'/QuoteTokenizer.php');

class ParamTokenizer {
	private $str, $tokens, $char, $quoteChars;
	public function __construct($str
		, $char = '?'
		, $quoteChars = array( array('"'), array("'") ))
	{
		$this->str = $str;
		$this->char = $char;
		$this->quoteChars = $quoteChars;
		$this->reset();
	}

	private function getNextTokenAfterQuote($str, $quote, $prev) {
		$len = strlen($str);
		$nextToken = strpos($str, $this->char, $prev);
		if($nextToken === false) {
			$nextToken = $len;
		}

		if($quote == null) {
			return $nextToken;
		}

		while($nextToken > $quote->start
			&& $nextToken < $quote->end)
		{
			$nextToken = strpos($str, $this->char, $nextToken+1);
			if($nextToken === false) {
				$nextToken = $len;
			}
		}
		return $nextToken;
	}

	private function tokenize($str) {
		$tokens = array();
		$quotes = new QuoteTokenizer($str, $this->quoteChars);
		$quote = $quotes->next();

		$len = strlen($str);
		$prev = 0;
		do {
			$p = $prev;
			do {
				$nextToken = $this->getNextTokenAfterQuote($str, $quote, $p);
				if($quote
					&& ($quote->start < $nextToken
					|| $quote->end < $nextToken))
				{
					$p = $quote->end;
					$quote = $quotes->next();
				}
			} while($quote && $quote->start < $nextToken);

			$token = substr($str, $prev, $nextToken - $prev);
			if($token === false) {
				$tokens[] = '';
				break;
			}
			$tokens[] = $token;
			$prev = $nextToken + 1;
		} while($prev - 1 < $len);
		
		return $tokens;
	}
	
	public function next() {
		$el = current($this->tokens);
		next($this->tokens);
		return $el;
	}

	public function reset() {
		$this->tokens = $this->tokenize($this->str);
	}

	public function count() {
		return sizeof($this->tokens)-1;
	}
}
