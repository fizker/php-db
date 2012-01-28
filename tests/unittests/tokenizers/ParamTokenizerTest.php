<?php

require_once(__DIR__.'/../../../src/tokenizers/ParamTokenizer.php');

use \sql\tokenizers\ParamTokenizer;

class ParamTokenizerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function next_NoParams_ReturnsString() {
		$tokenizer = new ParamTokenizer('abc');
		
		$result = $tokenizer->next();
		
		$this->assertSame('abc', $result);
	}

	/**
	 * @test
	 */
	public function next_NoParamsAndSecondCall_ReturnsNull() {
		$tokenizer = new ParamTokenizer('abc');
		
		$tokenizer->next();
		$result = $tokenizer->next();
		
		$this->assertSame(false, $result);
	}

	/**
	 * @test
	 */
	public function next_SingleParam_ReturnsSurroundingStrings() {
		$tokenizer = new ParamTokenizer('abc ? def');
		
		$first = $tokenizer->next();
		$second = $tokenizer->next();
		
		$this->assertSame('abc ', $first);
		$this->assertSame(' def', $second);
	}

	/**
	 * @test
	 */
	public function next_ParamInQuotes_ReturnsAllString() {
		$tokenizer = new ParamTokenizer('abc "?" def');
		
		$result = $tokenizer->next();
		
		$this->assertSame('abc "?" def', $result);
	}

	/**
	 * @test
	 */
	public function count_ParamIsFirstChar_Returns1() {
		$tokenizer = new ParamTokenizer('? abc');
		
		$result = $tokenizer->count();
		
		$this->assertSame(1, $result);
	}

	/**
	 * @test
	 */
	public function count_ParamIsLastChar_Returns1() {
		$tokenizer = new ParamTokenizer('abc ?');
		
		$result = $tokenizer->count();
		
		$this->assertSame(1, $result);
	}

	/**
	 * @test
	 */
	public function next_MultipleParams_AllAreTokenized() {
		$tokenizer = new ParamTokenizer('? abc "?" ? ab ?');
		
		$this->assertSame('', $tokenizer->next(), 'Before first param');
		$this->assertSame(' abc "?" ', $tokenizer->next(), 'Before second param');
		$this->assertSame(' ab ', $tokenizer->next(), 'Before last param');
		$this->assertSame('', $tokenizer->next(), 'After last param');
		$this->assertSame(false, $tokenizer->next(), 'No more tokens');
	}

	/**
	 * @test
	 */
	public function reset_TwoTokensFetched_FirstTokenIsRepeated() {
		$tokenizer = new ParamTokenizer('? abc "?" ? ab ?');
		
		$this->assertSame('', $tokenizer->next(), 'Before first param');
		$this->assertSame(' abc "?" ', $tokenizer->next(), 'Before second param');
		$tokenizer->reset();
		$this->assertSame('', $tokenizer->next(), 'Before first param');
		$this->assertSame(' abc "?" ', $tokenizer->next(), 'Before second param');
	}

	/**
	 * @test
	 */
	public function ctor_CharIsAltered_TokenizesCorrectly() {
		$tokenizer = new ParamTokenizer(', abc "," , ab ,', ',');
		
		$this->assertSame('', $tokenizer->next(), 'Before first param');
		$this->assertSame(' abc "," ', $tokenizer->next(), 'Before second param');
		$this->assertSame(' ab ', $tokenizer->next(), 'Before last param');
		$this->assertSame('', $tokenizer->next(), 'After last param');
		$this->assertSame(false, $tokenizer->next(), 'No more tokens');
	}

	/**
	 * @test
	 */
	public function ctor_QuotesAreAltered_TokenizesCorrectly() {
		$tokenizer = new ParamTokenizer(
			', abc (,) , ab ,'
			, ','
			, array( array('(',')') ));
		
		$this->assertSame('', $tokenizer->next(), 'Before first param');
		$this->assertSame(' abc (,) ', $tokenizer->next(), 'Before second param');
		$this->assertSame(' ab ', $tokenizer->next(), 'Before last param');
		$this->assertSame('', $tokenizer->next(), 'After last param');
		$this->assertSame(false, $tokenizer->next(), 'No more tokens');
	}

	/**
	 * @test
	 */
	public function next_StringContainsTwoQuotesBeforeToken_ReturnsCorrectly() {
		$tokenizer = new ParamTokenizer('abc "?" "?"');
		
		$result = $tokenizer->next();
		
		$this->assertSame('abc "?" "?"', $result);
	}

	/**
	 * @test
	 */
	public function ctor_UsedForColumns_TokenizesCorrectly() {
		$tokenizer = new ParamTokenizer(
			'`a` varchar(1) DEFAULT ","'
			, ','
			, array( array('(',')'), array('"'), array("'") )
		);
		
		$this->assertSame('`a` varchar(1) DEFAULT ","', $tokenizer->next(), 'The only param');
		$this->assertSame(false, $tokenizer->next(), 'No more tokens');
	}
}
?>