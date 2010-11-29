<?php

include("../lib/ScarletTokenReplacer.php");

class SimpleReplacementTest extends PHPUnit_Framework_Testcase {
	
	/**
	 * Test the simplest possible case - one token.
	 */
	public function testOneToken() {
		$string = "Just one {TOKEN}";
		$inputs = array("TOKEN" => "replacement here.");
		
		$expectedOutput = "Just one replacement here.";
		

		$tokenizer = new ScarletTokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
		
	}
	
	/**
	 * Test the case of two tokens.
	 */
	public function testTwoTokens() {
		$string = "This time, there are {count} {things}."
		$expectedOutput = "This time, there are several hams."
		
		$inputs = array("count" => "several", "things" => "hams");
		
		$tokenizer = new ScarletTokenizer->setSource($string)->setTokenFormat("{", "}")->setInputs($inputs);
		$this->assertEquals($tokenizer->replaceTokens(), $expectedOutput);
	}
	
	/**
	 * Check against an invalid token, with some extra characters up ins.
	 */
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
	 * Test that we get an exception if a token is not replaced.
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
	
	/**
	 * Test multi-line replacement
	 */
	public function testMultilineTokengroups() {
		$string = <<< END
Multi-line fun:
{MULTILINE}
	* {TEXT}
{/MULTILINE}		
END;
		$inputs = array("MULTILINE"=>array(
				"TEXT" => "for the whole family?",
				"TEXT" => "for little Timmy?",
				"TEXT" => "for nobody?"
			)
		);
	}
}

?>