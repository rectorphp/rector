<?php

declare (strict_types=1);
namespace Rector\PhpParser\ValueObject;

use PhpParser\Node\Stmt;
use PhpParser\Token;
final class StmtsAndTokens
{
    /**
     * @var Stmt[]
     * @readonly
     */
    private array $stmts;
    /**
     * @var array<int, Token>
     * @readonly
     */
    private array $tokens;
    /**
     * @param Stmt[] $stmts
     * @param array<int, Token> $tokens
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
     * @return array<int, Token>
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }
}
