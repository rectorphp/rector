<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\DataCollector;

use PhpParser\Node\Stmt\Use_;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UsePerFileInfoDataCollector
{
    /**
     * @var array<string, Use_[]>
     */
    private $usersPerRealPath = [];

    /**
     * @param Use_[] $uses
     */
    public function addUsesPerFileInfo(array $uses, SmartFileInfo $fileInfo): void
    {
        $this->usersPerRealPath[$fileInfo->getRealPath()] = $uses;
    }

    /**
     * @return Use_[]
     */
    public function getUsesByFileInfo(SmartFileInfo $fileInfo): array
    {
        return $this->usersPerRealPath[$fileInfo->getRealPath()] ?? [];
    }
}
