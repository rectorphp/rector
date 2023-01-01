<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202301\Nette\Utils\FileSystem;
final class FixtureTempFileDumper
{
    /**
     * @api
     * @var string
     */
    public const TEMP_FIXTURE_DIRECTORY = '/rector/tests_fixture_';
    public static function dump(string $fileContents, string $suffix = 'php') : string
    {
        // the "php" suffix is important, because that will hook into \Rector\Core\Application\FileProcessor\PhpFileProcessor
        $temporaryFileName = self::getTempDirectory() . '/' . \md5($fileContents) . '.' . $suffix;
        FileSystem::write($temporaryFileName, $fileContents);
        return $temporaryFileName;
    }
    /**
     * @api
     */
    public static function getTempDirectory() : string
    {
        return \sys_get_temp_dir() . self::TEMP_FIXTURE_DIRECTORY;
    }
}
