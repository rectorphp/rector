<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\EasyTesting\DataProvider;

use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210827\Symplify\SmartFileSystem\SmartFileSystem;
final class StaticFixtureUpdater
{
    public static function updateFixtureContent(\Symplify\SmartFileSystem\SmartFileInfo $originalFileInfo, string $changedContent, \Symplify\SmartFileSystem\SmartFileInfo $fixtureFileInfo) : void
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
    private static function getSmartFileSystem() : \RectorPrefix20210827\Symplify\SmartFileSystem\SmartFileSystem
    {
        return new \RectorPrefix20210827\Symplify\SmartFileSystem\SmartFileSystem();
    }
    private static function resolveNewFixtureContent(\Symplify\SmartFileSystem\SmartFileInfo $originalFileInfo, string $changedContent) : string
    {
        if ($originalFileInfo->getContents() === $changedContent) {
            return $originalFileInfo->getContents();
        }
        return $originalFileInfo->getContents() . '-----' . \PHP_EOL . $changedContent;
    }
}
