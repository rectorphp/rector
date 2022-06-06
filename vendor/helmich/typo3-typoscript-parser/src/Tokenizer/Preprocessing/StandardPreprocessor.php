<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Helmich\TypoScriptParser\Tokenizer\Preprocessing;

/**
 * Helper class that provides the standard pre-processing behaviour
 *
 * @package Helmich\TypoScriptParser\Tokenizer\Preprocessing
 */
class StandardPreprocessor extends ProcessorChain
{
    public function __construct(string $eolChar = "\n")
    {
        $this->processors = [new UnifyLineEndingsPreprocessor($eolChar), new RemoveTrailingWhitespacePreprocessor($eolChar)];
    }
}
