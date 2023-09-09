<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\ValueObject;

use PhpParser\Node\Stmt;
final class StmtsAndTokens
{
    /**
     * @var Stmt[]
     * @readonly
     */
    private $stmts;
    /**
     * @var array<int, (array{int, string, int} | string)>
     * @readonly
     */
    private $tokens;
    /**
     * @param Stmt[] $stmts
     * @param array<int, array{int, string, int}|string> $tokens
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
     * @return array<int, array{int, string, int}|string>
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }
}
