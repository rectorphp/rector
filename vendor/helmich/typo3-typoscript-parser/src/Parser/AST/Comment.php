<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Helmich\TypoScriptParser\Parser\AST;

final class Comment extends Statement
{
    /**
     * @var string
     */
    public $comment;
    public function __construct(string $comment, int $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->comment = $comment;
    }
}
