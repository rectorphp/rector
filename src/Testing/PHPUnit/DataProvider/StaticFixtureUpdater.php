<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit\DataProvider;

use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @deprecated
 * @todo use symplify/easy-testing once merged
 */
final class StaticFixtureUpdater
{
    public static function updateFixtureContent(
        SmartFileInfo $originalFileInfo,
        string $changedContent,
        SmartFileInfo $fixtureFileInfo
    ): void {
        if (! getenv('UPDATE_TESTS') && ! getenv('UT')) {
            return;
        }

        $newOriginalContent = self::resolveNewFixtureContent($originalFileInfo, $changedContent);

        self::getSmartFileSystem()->dumpFile($fixtureFileInfo->getRealPath(), $newOriginalContent);
    }

    private static function resolveNewFixtureContent(SmartFileInfo $originalFileInfo, string $changedContent): string
    {
        if ($originalFileInfo->getContents() === $changedContent) {
            return $originalFileInfo->getContents();
        }

        return $originalFileInfo->getContents() . '-----' . $changedContent;
    }

    private static function getSmartFileSystem(): SmartFileSystem
    {
        return new SmartFileSystem();
    }
}
