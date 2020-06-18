<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Symplify\SmartFileSystem\SmartFileInfo;

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
    public function addForRealPath(
        SmartFileInfo $smartFileInfo,
        array $newStmts,
        array $oldStmts,
        array $oldTokens
    ): void {
        $this->tokensByFilePath[$smartFileInfo->getRealPath()] = [$newStmts, $oldStmts, $oldTokens];
    }

    public function hasForFileInfo(SmartFileInfo $smartFileInfo): bool
    {
        return isset($this->tokensByFilePath[$smartFileInfo->getRealPath()]);
    }

    /**
     * @return Node[][]|Stmt[][]
     */
    public function getForFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->tokensByFilePath[$smartFileInfo->getRealPath()];
    }
}
