<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast;

use function trim;
class Comment
{
    public string $text;
    public int $startLine;
    public int $startIndex;
    public function __construct(string $text, int $startLine = -1, int $startIndex = -1)
    {
        $this->text = $text;
        $this->startLine = $startLine;
        $this->startIndex = $startIndex;
    }
    public function getReformattedText() : string
    {
        return trim($this->text);
    }
}
