<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Preprocessing;

/**
 * Preprocessor that unifies line endings for a file
 *
 * @package Helmich\TypoScriptParser\Tokenizer\Preprocessing
 */
class UnifyLineEndingsPreprocessor implements \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Preprocessing\Preprocessor
{
    /** @var string */
    private $eolCharacter;
    public function __construct(string $eolCharacter = "\n")
    {
        $this->eolCharacter = $eolCharacter;
    }
    /**
     * @param string $contents Un-processed Typoscript contents
     * @return string Processed TypoScript contents
     */
    public function preprocess(string $contents) : string
    {
        return \preg_replace(",(\r\n|\r|\n),", $this->eolCharacter, $contents);
    }
}
