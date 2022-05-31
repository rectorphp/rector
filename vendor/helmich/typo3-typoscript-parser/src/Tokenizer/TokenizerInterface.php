<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer;

/**
 * Interface TokenizerInterface
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
interface TokenizerInterface
{
    /**
     * @param string $inputString
     * @return TokenInterface[]
     */
    public function tokenizeString(string $inputString) : array;
    /**
     * @param string $inputStream
     * @return TokenInterface[]
     */
    public function tokenizeStream(string $inputStream) : array;
}
