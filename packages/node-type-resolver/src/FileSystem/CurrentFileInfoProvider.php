<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\FileSystem;

use PhpParser\Node;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CurrentFileInfoProvider
{
    /**
     * @var SmartFileInfo|null
     */
    private $smartFileInfo;

    /**
     * @var Node[]
     */
    private $currentStmts = [];

    public function setCurrentFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->smartFileInfo = $smartFileInfo;
    }

    public function getSmartFileInfo(): ?SmartFileInfo
    {
        return $this->smartFileInfo;
    }

    /**
     * @param Node[] $stmts
     */
    public function setCurrentStmt(array $stmts): void
    {
        $this->currentStmts = $stmts;
    }

    /**
     * @return Node[]
     */
    public function getCurrentStmts(): array
    {
        return $this->currentStmts;
    }
}
