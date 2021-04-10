<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Exception\ShouldNotHappenException;
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
        if (! $this->hasForFileInfo($smartFileInfo)) {
            throw new ShouldNotHappenException(sprintf(
                'File "%s" was not preparsed, so it cannot be printed.%sCheck "%s" method.',
                $smartFileInfo->getRealPath(),
                PHP_EOL,
                self::class . '::parseFileInfoToLocalCache()'
            ));
        }

        return $this->tokensByFilePath[$smartFileInfo->getRealPath()];
    }
}
