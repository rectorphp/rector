<?php

declare (strict_types=1);
namespace RectorPrefix20210704\Helmich\TypoScriptParser\Parser\AST;

final class Comment extends \RectorPrefix20210704\Helmich\TypoScriptParser\Parser\AST\Statement
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
