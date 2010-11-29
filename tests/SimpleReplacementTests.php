<?php

class SimpleReplacementTest extends PHPUnit_Framework_Testcase {
	
	public function testOneToken() {
		$string = "Just one {TOKEN}";
		$inputs = array("TOKEN" => "replacement here.");
		
		$expectedOutput = "Just one replacement here.";
		
		$tokenizer = new ScarletTokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
		
	}
	
	public function testTwoTokens() {
		$string = "This time, there are {count} {things}."
		$expectedOutput = "This time, there are several hams."
		
		$inputs = array("count" => "several", "things" => "hams");
		
		$tokenizer = new ScarletTokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
	}
	
}

?>