<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer;

class ScannerLine
{
    private $line;
    private $index;
    private $original;
    public function __construct(int $index, string $line)
    {
        $this->line = $line;
        $this->original = $line;
        $this->index = $index;
    }
    /**
     * @param string $pattern
     * @return array|false
     */
    public function scan($pattern)
    {
        if (\preg_match($pattern, $this->line, $matches)) {
            $this->line = \substr($this->line, \strlen($matches[0])) ?: "";
            return $matches;
        }
        return \false;
    }
    /**
     * @param string $pattern
     * @return string[]|false
     */
    public function peek($pattern)
    {
        if (\preg_match($pattern, $this->line, $matches)) {
            return $matches;
        }
        return \false;
    }
    public function index() : int
    {
        return $this->index;
    }
    public function value() : string
    {
        return $this->line;
    }
    public function length() : int
    {
        return \strlen($this->line);
    }
    public function __toString() : string
    {
        return $this->original;
    }
}
