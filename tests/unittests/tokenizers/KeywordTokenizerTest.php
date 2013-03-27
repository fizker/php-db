<?php

require_once(__DIR__.'/../../../src/tokenizers/KeywordTokenizer.php');

use \sql\tokenizers\KeywordTokenizer;

class KeywordTokenizerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function next_keywordIsFirst_returnsKeyword() {
		$keywords = new KeywordTokenizer('ab CD ef', array('AB'));

		$p1 = $keywords->next();

		$this->assertEquals('AB', $p1->value);
		$this->assertTrue($p1->isKeyword);
	}

	/**
	 * @test
	 */
	public function next_simpleInput_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab CD ef', array('CD'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('ab', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('CD', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('ef', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function next_incorrectCasing_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab cd ef', array('CD'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('ab', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('CD', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('ef', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function next_nonKeywordHasSpaces_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab cd EF gh', array('EF'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('ab cd', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('EF', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('gh', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function next_quotedNonKeyword_iteratesProperly() {
		$keywords = new KeywordTokenizer('"ab CD ef" CD gh', array('CD'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('"ab CD ef"', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('CD', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('gh', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function next_quotedKeyword_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab `EF` EF gh', array('EF'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('ab `EF`', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('EF', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('gh', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function next_keywordHasSpaces_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab CD EF gh', array('CD EF'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();

		$this->assertEquals('ab', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('CD EF', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('gh', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertNull($p4);
	}

	/**
	 * @test
	 */
	public function foreach_tokenizerIsUsedInForeach_iteratesCorrectly() {
		$keywords = new KeywordTokenizer('ab CD ef', array('CD'));
		$tokens = array();

		foreach($keywords as $token) {
			$tokens[] = array($token->value, $token->isKeyword);
		}

		$this->assertEquals(
			  array(
			    array('ab', false)
			  , array('CD', true)
			  , array('ef', false)
			)
			, $tokens
		);
	}

	/**
	 * @test
	 */
	public function next_multipleKeywords_iteratesProperly() {
		$keywords = new KeywordTokenizer('ab CD ef GH ij', array('CD', 'GH'));

		$p1 = $keywords->next();
		$p2 = $keywords->next();
		$p3 = $keywords->next();
		$p4 = $keywords->next();
		$p5 = $keywords->next();
		$p6 = $keywords->next();

		$this->assertEquals('ab', $p1->value);
		$this->assertFalse($p1->isKeyword);

		$this->assertEquals('CD', $p2->value);
		$this->assertTrue($p2->isKeyword);

		$this->assertEquals('ef', $p3->value);
		$this->assertFalse($p3->isKeyword);

		$this->assertEquals('GH', $p4->value);
		$this->assertTrue($p4->isKeyword);

		$this->assertEquals('ij', $p5->value);
		$this->assertFalse($p5->isKeyword);

		$this->assertNull($p6);
	}
}
