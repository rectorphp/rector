<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt;

final class TokensByFilePathStorage
{
    /**
     * @todo use value object
     * @var Node[][][]|Stmt[][][]
     */
    private $tokensByFilePath = [];

    /**
     * @todo replace with SmartFileInfo $realPath
     *
     * @param Node[]|Stmt[] $newStmts
     * @param Node[]|Stmt[] $oldStmts
     * @param Node[]|Stmt[] $oldTokens
     */
    public function addForRealPath(string $realPath, array $newStmts, array $oldStmts, array $oldTokens): void
    {
        $this->tokensByFilePath[$realPath] = [$newStmts, $oldStmts, $oldTokens];
    }

    /**
     * @todo replace with SmartFileInfo $realPath
     */
    public function hasForRealPath(string $realPath): bool
    {
        return isset($this->tokensByFilePath[$realPath]);
    }

    /**
     * @todo replace with SmartFileInfo $realPath
     *
     * @return Node[][]|Stmt[][]
     */
    public function getForRealPath(string $realPath): array
    {
        return $this->tokensByFilePath[$realPath];
    }
}
