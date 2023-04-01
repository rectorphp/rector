<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202304\Nette\Utils\FileSystem;
final class FixtureFileUpdater
{
    /**
     * @api
     */
    public static function updateFixtureContent(string $originalFilePath, string $changedContent, string $fixtureFilePath) : void
    {
        if (!\getenv('UPDATE_TESTS') && !\getenv('UT')) {
            return;
        }
        $newOriginalContent = self::resolveNewFixtureContent($originalFilePath, $changedContent);
        FileSystem::write($fixtureFilePath, $newOriginalContent);
    }
    private static function resolveNewFixtureContent(string $originalFilePath, string $changedContent) : string
    {
        $originalContent = FileSystem::read($originalFilePath);
        if ($originalContent === $changedContent) {
            return $originalContent;
        }
        return $originalContent . '-----' . \PHP_EOL . $changedContent;
    }
}
