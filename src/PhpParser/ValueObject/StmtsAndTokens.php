<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PhpParser\ValueObject;

use RectorPrefix20220606\PhpParser\Node\Stmt;
final class StmtsAndTokens
{
    /**
     * @var Stmt[]
     * @readonly
     */
    private $stmts;
    /**
     * @var mixed[]
     * @readonly
     */
    private $tokens;
    /**
     * @param Stmt[] $stmts
     * @param mixed[] $tokens
     */
    public function __construct(array $stmts, array $tokens)
    {
        $this->stmts = $stmts;
        $this->tokens = $tokens;
    }
    /**
     * @return Stmt[]
     */
    public function getStmts() : array
    {
        return $this->stmts;
    }
    /**
     * @return mixed[]
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }
}
