<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST;

final class MultilineComment extends \Helmich\TypoScriptParser\Parser\AST\Statement
{
    /**
     * @var string
     */
    public $comment;
    public function __construct(string $comment, int $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->comment = \preg_replace('/[\\0\\r\\x0B\\t]/', '', $comment);
    }
}
