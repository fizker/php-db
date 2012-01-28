<?php

require_once(__DIR__.'/../../../src/tokenizers/QuoteTokenizer.php');

use \sql\tokenizers\QuoteTokenizer;
use \sql\tokenizers\Quote;

class QuoteTokenizerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function next_NoQuotes_ReturnsNull() {
		$quotes = new QuoteTokenizer('abc def');
		
		$result = $quotes->next();
		
		$this->assertEquals(null, $result);
	}

	/**
	 * @test
	 * @dataProvider provider_next_OneQuote_ReturnsQuote
	 */
	public function next_OneQuote_ReturnsQuote($str, $expected) {
		$quotes = new QuoteTokenizer($str);
		
		$result = $quotes->next();
		
		$this->assertEquals($expected, $result);
	}
	public function provider_next_OneQuote_ReturnsQuote() {
		return array(
			array('abc "def" ghi', new Quote('def', '"', 4)),
			array("abc 'def' ghi", new Quote('def', "'", 4))
		);
	}

	/**
	 * @test
	 */
	public function next_TwoQuotes_ReturnsInRightOrder() {
		$quotes = new QuoteTokenizer('abc "def" ghi "jkl"');
		
		$first = $quotes->next();
		$second = $quotes->next();
		
		$expected = new Quote('def', '"', 4);
		$this->assertEquals($expected, $first, 'Fetching first quote');

		$expected = new Quote('jkl', '"', 14);
		$this->assertEquals($expected, $second, 'Fetching second quote');
	}

	/**
	 * @test
	 * @dataProvider provider_next_AfterLastQuote_ReturnsNull
	 */
	public function next_AfterLastQuote_ReturnsNull($str, $expected) {
		$quotes = new QuoteTokenizer($str);
		
		$first = $quotes->next();
		$second = $quotes->next();
		
		$this->assertEquals($expected, $first, 'Fetching first quote');

		$this->assertEquals(null, $second, 'Fetching second quote');
	}
	public function provider_next_AfterLastQuote_ReturnsNull() {
		return array(
			array('abc "def" ghi', new Quote('def', '"', 4)),
			array('abc "d""ef" ghi', new Quote('d""ef', '"', 4)),
			array("abc 'def' ghi", new Quote('def', "'", 4))
		);
	}

	/**
	 * @test
	 * @dataProvider provider_next_QuoteContainsEscaped_ReturnsEntireQuote
	 */
	public function next_QuoteContainsEscaped_ReturnsEntireQuote($str, $expected) {
		$quotes = new QuoteTokenizer($str);
		
		$quote = $quotes->next();
		
		$this->assertEquals($expected, $quote);
	}
	public function provider_next_QuoteContainsEscaped_ReturnsEntireQuote() {
		return array(
			array('abc "d""ef" ghi', new Quote('d""ef', '"', 4)),
			array('abc "d""e""f" ghi', new Quote('d""e""f', '"', 4)),
			array("abc 'd''ef' ghi", new Quote("d''ef", "'", 4)),
			array("abc 'd''e''f' ghi", new Quote("d''e''f", "'", 4))
		);
	}

	/**
	 * @test
	 */
	public function next_MixedQuotes_ReturnsBothCorrectly() {
		$quotes = new QuoteTokenizer('abc "d\'ef" \'g"hi\' "hij" klm');
		
		$first = $quotes->next();
		$second = $quotes->next();
		
		$expected = new Quote("d'ef", '"', 4);
		$this->assertEquals($expected, $first, 'Fetching first quote');

		$expected = new Quote('g"hi', "'", 11);
		$this->assertEquals($expected, $second, 'Fetching second quote');
	}

	/**
	 * @test
	 */
	public function reset_AtStart_FirstQuoteIsNext() {
		$quotes = new QuoteTokenizer('abc "def" ghi');
		
		$quotes->reset();
		
		$quote = $quotes->next();
		
		$expected = new Quote('def', '"', 4);
		$this->assertEquals($expected, $quote);
	}

	/**
	 * @test
	 */
	public function reset_AfterFirstQuote_FirstQuoteIsNext() {
		$quotes = new QuoteTokenizer('abc "def" ghi');
		
		$quotes->next();
		$quotes->reset();
		
		$quote = $quotes->next();
		
		$expected = new Quote('def', '"', 4);
		$this->assertEquals($expected, $quote);
	}

	/**
	 * @test
	 */
	public function ctor_DifferentQuoteCharsGiven_NewCharsRespected() {
		$quotes = new QuoteTokenizer('abc "d" \'e\' (f) ghi', array(
			array('(',')'), array('"')
		));
		
		$expected = new Quote('d', '"', 4);
		$this->assertEquals($expected, $quotes->next());

		$expected = new Quote('f', '(', 12);
		$this->assertEquals($expected, $quotes->next());

		$expected = null;
		$this->assertEquals($expected, $quotes->next());
	}

	/**
	 * @test
	 */
	public function ctor_MoreThanThreeQuoteChars_TokenizesCorrectly() {
		$quotes = new QuoteTokenizer('abc "d" \'e\' (f) ghi', array(
			array('(',')'), array('"'), array("'")
		));
		
		$expected = new Quote('d', '"', 4);
		$this->assertEquals($expected, $quotes->next());

		$expected = new Quote('e', "'", 8);
		$this->assertEquals($expected, $quotes->next());

		$expected = new Quote('f', '(', 12);
		$this->assertEquals($expected, $quotes->next());

		$expected = null;
		$this->assertEquals($expected, $quotes->next());
	}

	/**
	 * @test
	 */
	public function ctor_UsedForColumns_TokenizesCorrectly() {
		$quotes = new QuoteTokenizer(
			'`a` varchar(1) DEFAULT ",",
			`b` varchar(1) DEFAULT \',\',
			`c` enum("1", "2")'
			, array( array('(',')'), array('"'), array("'") )
		);
		
		$expected = new Quote('1', '(', 11);
		$this->assertEquals($expected, $quotes->next());
		
		$expected = new Quote(',', '"', 23);
		$this->assertEquals($expected, $quotes->next());

		$expected = new Quote('1', '(', 42);
		$this->assertEquals($expected, $quotes->next());
		
		$expected = new Quote(',', "'", 54);
		$this->assertEquals($expected, $quotes->next());

		$expected = new Quote('"1", "2"', '(', 70);
		$this->assertEquals($expected, $quotes->next());

		$expected = null;
		$this->assertEquals($expected, $quotes->next());
	}
}
?>