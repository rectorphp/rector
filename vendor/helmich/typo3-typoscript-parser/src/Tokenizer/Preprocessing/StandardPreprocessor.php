<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\Preprocessing;

/**
 * Helper class that provides the standard pre-processing behaviour
 *
 * @package Helmich\TypoScriptParser\Tokenizer\Preprocessing
 */
class StandardPreprocessor extends \RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\Preprocessing\ProcessorChain
{
    public function __construct(string $eolChar = "\n")
    {
        $this->processors = [new \RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\Preprocessing\UnifyLineEndingsPreprocessor($eolChar), new \RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\Preprocessing\RemoveTrailingWhitespacePreprocessor($eolChar)];
    }
}
