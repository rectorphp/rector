<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer;

use Iterator;
/**
 * Helper class for scanning lines
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class Scanner implements \Iterator
{
    /** @var string[] */
    private $lines = [];
    /** @var int */
    private $index = 0;
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }
    public function current() : \RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\ScannerLine
    {
        return new \RectorPrefix20220501\Helmich\TypoScriptParser\Tokenizer\ScannerLine($this->index + 1, $this->lines[$this->index]);
    }
    public function next() : void
    {
        $this->index++;
    }
    public function key() : int
    {
        return $this->index;
    }
    public function valid() : bool
    {
        return $this->index < \count($this->lines);
    }
    public function rewind() : void
    {
        $this->index = 0;
    }
}
