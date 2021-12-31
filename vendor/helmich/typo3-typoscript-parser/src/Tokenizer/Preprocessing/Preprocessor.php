<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Helmich\TypoScriptParser\Tokenizer\Preprocessing;

/**
 * Interface definitions for tokenizer preprocessors.
 *
 * Preprocessors can change the TypoScript input source code before it
 * is passed to the actual tokenizer.
 *
 * @package Helmich\TypoScriptParser\Tokenizer\Preprocessing
 */
interface Preprocessor
{
    /**
     * @param string $contents Un-processed Typoscript contents
     * @return string Processed TypoScript contents
     */
    public function preprocess(string $contents) : string;
}
