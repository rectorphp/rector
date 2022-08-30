<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202208\Nette\Utils\FileSystem;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FixtureTempFileDumper
{
    /**
     * @var string
     */
    public const TEMP_FIXTURE_DIRECTORY = '/rector/tests_fixture_';
    public static function dump(string $fileContents, string $suffix = 'php') : SmartFileInfo
    {
        // the "php" suffix is important, because that will hook into \Rector\Core\Application\FileProcessor\PhpFileProcessor
        $temporaryFileName = \sys_get_temp_dir() . self::TEMP_FIXTURE_DIRECTORY . '/' . \md5($fileContents) . '.' . $suffix;
        FileSystem::write($temporaryFileName, $fileContents);
        return new SmartFileInfo($temporaryFileName);
    }
}
