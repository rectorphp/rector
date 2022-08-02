<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\EasyTesting\DataProvider;

use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @api
 */
final class StaticFixtureUpdater
{
    /**
     * @param \Symplify\SmartFileSystem\SmartFileInfo|string $originalFileInfo
     */
    public static function updateFixtureContent($originalFileInfo, string $changedContent, SmartFileInfo $fixtureFileInfo) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        $newOriginalContent = self::resolveNewFixtureContent($originalFileInfo, $changedContent);
        self::getSmartFileSystem()->dumpFile($fixtureFileInfo->getRealPath(), $newOriginalContent);
    }
    public static function updateExpectedFixtureContent(string $newOriginalContent, SmartFileInfo $expectedFixtureFileInfo) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        self::getSmartFileSystem()->dumpFile($expectedFixtureFileInfo->getRealPath(), $newOriginalContent);
    }
    private static function getSmartFileSystem() : SmartFileSystem
    {
        return new SmartFileSystem();
    }
    /**
     * @param \Symplify\SmartFileSystem\SmartFileInfo|string $originalFileInfo
     */
    private static function resolveNewFixtureContent($originalFileInfo, string $changedContent) : string
    {
        if ($originalFileInfo instanceof SmartFileInfo) {
            $originalContent = $originalFileInfo->getContents();
        } else {
            $originalContent = $originalFileInfo;
        }
        if ($originalContent === $changedContent) {
            return $originalContent;
        }
        return $originalContent . '-----' . \PHP_EOL . $changedContent;
    }
}
