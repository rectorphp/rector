<?php

declare (strict_types=1);
namespace RectorPrefix20210909\Helmich\TypoScriptParser\Tokenizer\Preprocessing;

/**
 * Preprocessor that does not actually do anything
 *
 * @package Helmich\TypoScriptParser\Tokenizer\Preprocessing
 */
class NoOpPreprocessor implements \RectorPrefix20210909\Helmich\TypoScriptParser\Tokenizer\Preprocessing\Preprocessor
{
    /**
     * @param string $contents Un-processed Typoscript contents
     * @return string Processed TypoScript contents
     */
    public function preprocess($contents) : string
    {
        return $contents;
    }
}
