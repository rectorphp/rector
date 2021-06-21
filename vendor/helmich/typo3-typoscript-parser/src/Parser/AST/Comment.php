<?php

declare (strict_types=1);
namespace RectorPrefix20210621\Helmich\TypoScriptParser\Parser\AST;

final class Comment extends \RectorPrefix20210621\Helmich\TypoScriptParser\Parser\AST\Statement
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
