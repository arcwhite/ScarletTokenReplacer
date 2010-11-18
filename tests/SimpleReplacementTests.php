<?php

include("../lib/ScarletTokenReplacer.php");

class SimpleReplacementTests extends PHPUnit_Framework_Testcase {
	
	public function testOneToken() {
		$string = "Just one {TOKEN}";
		$inputs = array("TOKEN" => "replacement here.");
		
		$expectedOutput = "Just one replacement here.";
		
		$tokenizer = new ScarletTokenReplacer();
		$tokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
		
	}
	
	public function testTwoTokens() {
		$string = "This time, there are {count} {things}.";
		$expectedOutput = "This time, there are several hams.";
		
		$inputs = array("count" => "several", "things" => "hams");
		
		$tokenizer = new ScarletTokenReplacer();
		$tokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
	}
	
	public function testWithTokenCharsInString() {
		$string = "Just one {TOKEN}, plus some } { extra bits.{ foo? }";
		// Note that { foo? } is not a valid tag - tags must have no spaces after separators
		$inputs = array("TOKEN" => "replacement here");
		
		$expectedOutput = "Just one replacement here, plus some } { extra bits.{ foo? }";
		
		$tokenizer = new ScarletTokenReplacer();
		$tokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testUnreplacedTokenException() {
		
		$string = "<<TOKEN>> <<TOKEN2>>";
		$inputs = array("TOKEN" => "Chunky bacon");
		
		try {
			$tokenizer = new ScarletTokenReplacer();
			$tokenizer->setSource($string)->setTokenFormat("<<", ">>")->setInputs($inputs);
			$tokenizer->replaceTokens();
		} catch(Exception $e) {
			return;
		}
		
		$this->fail("Expected exception, and none was forthcoming.");
		
	}
	
}

?>