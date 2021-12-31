<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\EasyTesting\DataProvider;

use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211231\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @api
 */
final class StaticFixtureUpdater
{
    /**
     * @param string|\Symplify\SmartFileSystem\SmartFileInfo $originalFileInfo
     */
    public static function updateFixtureContent($originalFileInfo, string $changedContent, \Symplify\SmartFileSystem\SmartFileInfo $fixtureFileInfo) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        $newOriginalContent = self::resolveNewFixtureContent($originalFileInfo, $changedContent);
        self::getSmartFileSystem()->dumpFile($fixtureFileInfo->getRealPath(), $newOriginalContent);
    }
    public static function updateExpectedFixtureContent(string $newOriginalContent, \Symplify\SmartFileSystem\SmartFileInfo $expectedFixtureFileInfo) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        self::getSmartFileSystem()->dumpFile($expectedFixtureFileInfo->getRealPath(), $newOriginalContent);
    }
    private static function getSmartFileSystem() : \RectorPrefix20211231\Symplify\SmartFileSystem\SmartFileSystem
    {
        return new \RectorPrefix20211231\Symplify\SmartFileSystem\SmartFileSystem();
    }
    /**
     * @param string|\Symplify\SmartFileSystem\SmartFileInfo $originalFileInfo
     */
    private static function resolveNewFixtureContent($originalFileInfo, string $changedContent) : string
    {
        if ($originalFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
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
