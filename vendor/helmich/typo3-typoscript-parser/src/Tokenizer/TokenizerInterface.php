<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer;

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
    public function tokenizeString($inputString) : array;
    /**
     * @param string $inputStream
     * @return TokenInterface[]
     */
    public function tokenizeStream($inputStream) : array;
}
