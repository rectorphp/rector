<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202409\Nette\Utils\FileSystem;
final class FixtureFileUpdater
{
    /**
     * @api
     */
    public static function updateFixtureContent(string $originalContent, string $changedContent, string $fixtureFilePath) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        $newOriginalContent = self::resolveNewFixtureContent($originalContent, $changedContent);
        FileSystem::write($fixtureFilePath, $newOriginalContent, null);
    }
    private static function resolveNewFixtureContent(string $originalContent, string $changedContent) : string
    {
        if ($originalContent === $changedContent) {
            return $originalContent;
        }
        return $originalContent . '-----' . \PHP_EOL . $changedContent;
    }
}
