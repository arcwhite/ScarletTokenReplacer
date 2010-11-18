A trivially simple token-replacement tool (I hesitate to call it a 'library').
Given a token format (ie. '<<TOKEN>>' or '{TOKEN}') and a set of key-value pairs, we parse some body of text and replace those tokens with the appropriate values.

# DISCLAIMER

I'm putting this code up more in an effort to 'get stuff out there' than because I think you should use this particular chunk of code.

This code was pretty basic, hacked together in a matter of a few hours for a particular client, whose needs were pretty specific (generating RTF files from a template based on user input to a web form, and possibly later extending the template without our input and without having to teach their staff PHP).

It's not the most elegant way to do this, and the code is pretty primitive. Caveat emptor.

# BASIC USAGE

A simple example...

<code>
    $string = "This is some <<TEXT>>. Tokens will be replaced by appropriate <<VALUES>>.";

    $keysAndValues = array('TEXT'=>'tasty text', 'VALUES' =>'good times');

    $replaced_string = new ScarletTokenizer()
					->setSource($string)
					->setTokenFormat('<<', '>>')
					->setInputs($keysAndValues)
					->replaceTokens();

    echo $replaced_string;
</code>

Outputs:
<code>
    This is some tasty text. Tokens will be replaced by appropriate good times.
</code>

# REPEATING REPLACEMENTS

You can also do more complex things with repeating replacements. If a key-value pair in the input is an array, the corresponding token will be treated as containing other tokens that are to be replaced for all values. Alright, that's hard to explain in text, so let me demonstrate:

<code>
    $string = <<<EOT
    This is some more text. {TOKEN1}
    {MULTILINE}
	    Field 1: {Value1}
	    Field 2: {Value2}
    {/MULTILINE}
    <<<EOT;

    $keysAndValues = array(
					'TOKEN1'=>'Obviously.', 
					'MULTILINE' => array(
						array(
							'Value1' => 'Hello',
							'Value2' => 'World'
						),
						array(
							'Value1' => 'Foo',
							'Value2' => 'Bar'
						)
 				 	)
				);

    $replaced_string = new ScarletTokenizer()
					->setSource($string)
					->setTokenFormat('<<', '>>')
					->setInputs($keysAndValues)
					->replaceTokens();

    echo $replaced_string;
</code>

Outputs:
<code>
    This is some more text. Obviously.

	    Field 1: Hello
	    Field 2: World

	    Field 1: Foo
	    Field 2: Bar
</code>

