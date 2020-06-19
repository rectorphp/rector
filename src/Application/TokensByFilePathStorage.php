<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\ValueObject\Application\ParsedStmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TokensByFilePathStorage
{
    /**
     * @var ParsedStmtsAndTokens[]
     */
    private $tokensByFilePath = [];

    public function addForRealPath(SmartFileInfo $smartFileInfo, ParsedStmtsAndTokens $parsedStmtsAndTokens): void
    {
        $this->tokensByFilePath[$smartFileInfo->getRealPath()] = $parsedStmtsAndTokens;
    }

    public function hasForFileInfo(SmartFileInfo $smartFileInfo): bool
    {
        return isset($this->tokensByFilePath[$smartFileInfo->getRealPath()]);
    }

    public function getForFileInfo(SmartFileInfo $smartFileInfo): ParsedStmtsAndTokens
    {
        return $this->tokensByFilePath[$smartFileInfo->getRealPath()];
    }
}
