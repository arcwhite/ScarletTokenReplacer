<?php

/**
 * Scarlet Token Replacer.
 * Given an array of key-value pairs, replace tokens in some particular format in body of text.
 * It sounds pretty simple, and it is, but I couldn't find a good pre-built implementation.
 *
 * Example use:
 * $tokenizer = new ScarletTokenReplacer()
 *					->setSource($string)
 *					->setTokenFormat('<<', '>>')
 *					->setInputs($keyValueArray)
 *					->replaceTokens();
 */

class ScarletTokenReplacer {
	
	/**
	 * @var string
	 */
	private $_source;
	
	
	/**
	 * @var integer
	 */
	private $_countTokenKeys;
	
	/**
	 * @var string
	 */
	private $_openToken;
	
	/**
	 * @var string
	 */
	private $_closeToken;
	
	/**
	 * @var array
	 */	
	private $_inputs;
	
	/**
	 * @var array
	 */
	private $_keys;
	
	/**
	 * Return self on construction.
	 */
	function __construct() {
		return $this;
	}

	/**
	 * Set the source material - the text that will have stuff replaced.
	 * @param string $string
	 */
	function setSource($string=NULL) {
		if($string == NULL || strlen($string) == 0) {
			throw new Exception("String must be provided");
		}
		
		$this->_source = $string;
		return $this;
	}
	
	/**
	 * Ensures that the source is set.
	 */
	private function verifySource() {
		if($this->_source == NULL || strlen($this->_source) == 0) {
			throw new Exception("You must first specify the string to be tokenized");
		}
		
		return true;
	}
	
	/**
	 * Set the token format. As a side-effect, set the count of key values in the source material.
	 */
	function setTokenFormat($stringOpen="<<", $stringClose=">>") {
		$this->verifySource();
		
		if(strlen($stringOpen) == 0) {
			throw new Exception("Token format specification must have a non-zero-length opening tag");
		}

		if(strlen($stringClose) == 0) {
			throw new Exception("Token format specification must have a non-zero-length closing tag");
		}
		
		$this->_openToken = $stringOpen; //@todo Escape it
		$this->_closeToken = $stringClose;
		
		$this->enumerateTokenKeys();
		
		return $this;
	}
	
	/**
	 * Use a regexp to enumerate and record the tokens inside the source document.
	 */
	private function enumerateTokenKeys() {
		
		$single_pattern = $this->_openToken.'[^\/^'.$this->_closeToken.'^\s]+(\w?)'.$this->_closeToken.'+';
		$pattern = "/".$single_pattern."/Usi";
		$matches = array();
		$result = preg_match_all($pattern, $this->_source, &$matches);
		
		foreach($matches[0] as $match) {
			$keyName = str_replace(array($this->_openToken, $this->_closeToken), "", $match);
			$this->_keys[$keyName] = 1;
		}
		
		return $this;
	}

	/** 
	 * Ensure the token format is set - it must not be null and must be at least one character.
	 * Open and close of the token can be different if desired (e.g. <<TOKEN| works)
	 * @throws Exception
	 */
	private function verifyTokenFormat() {
		if($this->_openToken == NULL || strlen($this->_openToken) == 0 || $this->_closeToken == NULL || strlen($this->_closeToken) == 0) {
			throw new Exception("You must first specify the token format");
		}
		
		return true;
	}

	/**
	 * Flatten nested array of keys into an array of values. 
	 * Thus, array(key1 => array(key11=>value, key12=>value))
	 * becomes array(key1, key11, key12).
	 */
	private function array_flatten_keys($array, $return=array()) {
		foreach ($array AS $key => $value) {
			// If the value for this key is an array, flatten it in turn
			// and extract the keys
			if(is_array($value)) {
				$return = $this->array_flatten_keys($value,$return);
				if(is_string($key)) {
					$return[] = $key;
				}
				continue; // Go to next value
			} 	
			
			// Value is not an array, so we can take the key
			// without further work.
			$return[] = $key;			
		}
		return $return;

	}

	/**
	 * Set the inputs as an array of key->value pairs. Number of keys must match number of keys
	 * in document. (We throw an exception if any token in the document is not replaced)
	 */
	function setInputs($inputs=NULL) {
		$this->verifySource();
		$this->verifyTokenFormat();

		if($inputs == NULL || !is_array($inputs) || count($inputs) == 0) {
			throw new Exception("You must specify inputs as an array");
		}
		// Get an array of all keys in the input
		$inputKeys = $this->array_flatten_keys($inputs);
		// Get an array of all keys in the source text
		$tokenKeys = array_keys($this->_keys);
		
		$diff_inputs = array_diff($inputKeys, $tokenKeys);
		if(count($diff_inputs) > 0) {
			// There's inputs not matches in the source
			// (Remember, array_diff(A, B) is not array_diff(B, A))
			throw new Exception("Unhandled keys in input: ".implode(",",$diff_inputs));
		}
		
		$diff_tokens = array_diff($tokenKeys, $inputKeys);
		if(count($diff_tokens) > 0) {
			// There's tokens in the source not matches in the input
			// (Remember, array_diff(A, B) is not array_diff(B, A))
			throw new Exception("Unhandled keys in source string: ".implode(",",$diff_tokens));
		}
		
		$this->_inputs = $inputs;
		
		return $this;
	}

	/**
	 * Confirm that the key-value pairs have been provided
	 */
	function verifyInputs() {
		if(!is_array($this->_inputs) || count($this->_inputs) == 0) {
			throw new Exception("Inputs not provided. Please provide input key->value pairs.");
		}
		
		return true;
	}

	/** 
	 * Change the tokens for the values specified based on the keys.
	 * Return the modified string.
	 * Sing a jaunty tune of victory!
	 */
	function replaceTokens() {
		$this->verifySource();
		$this->verifyTokenFormat();
		$this->verifyInputs();
		
		// Iterate over the inputs...
		// This is kind of naive, really, but it works.
		foreach($this->_inputs as $key => $value) {
			// We determine if a token is multi-line based on the inputs.
			// Potentially quite dumb, but quick-and-easy for this use case.
			// NOTE: If you don't supply an array input for a multi-line token group,
			// this will fall over horribly.
			if(is_array($value)) {
				// This is a repeating line of items
				$linepattern = '%'.$this->_openToken.$key.$this->_closeToken.'\\n+(.*?)'.$this->_openToken.'/'.$key.$this->_closeToken.'+%Uis';

				$matches = array();
				$result = preg_match_all($linepattern, $this->_source, &$matches);

				$fieldset = "";
				foreach($value as $line) {
					// Repeat for each input
					$newline = $matches[1][0];
					foreach($line as $fieldKey => $fieldValue) {
						// Replace the values
						$newline = str_replace($this->_openToken.$fieldKey.$this->_closeToken, $fieldValue, $newline);
					}
					// Aggregate - this will probably fall over if the inputs/source are
					// big enough and your PHP memory limits low enough. 
					$fieldset .= $newline;
				}
				// Replace the pair of outer tokens, plus their contents, with the 
				// computed string.
				$this->_source = str_replace($matches[0][0], $fieldset, $this->_source);
				
			} else {
				// The input is just a value, so simple replacement will do.
				$this->_source = str_replace($this->_openToken.$key.$this->_closeToken, $value, $this->_source);
			}
		}
		
		return $this->_source;
	}

}

?>