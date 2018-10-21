<?php declare(strict_types=1);

namespace Rector\Tests\Composer;

use Composer\Script\Event;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class AutoloadWrongCasesEventSubscriber
{
    /**
     * @var string[]
     */
    private static $wrongDirectories = [];

    /**
     * @see https://www.drupal.org/files/issues/vendor-classmap-2468499-14.patch
     */
    public static function preAutoloadDump(Event $event)
    {
        $paths = [
            __DIR__ . '/../../tests/Rector/*',
            __DIR__ . '/../../tests/Issues/*',
            __DIR__ . '/../../packages/*'
        ];

        foreach ($paths as $path) {
            self::$wrongDirectories = array_merge(self::$wrongDirectories, self::getWrongDirectoriesInPath($path));
        }

        $package = $event->getComposer()->getPackage();
        $autoload = $package->getDevAutoload();
        $autoload['classmap'] = array_merge($autoload['classmap'] ?? [], self::$wrongDirectories);
        $package->setDevAutoload($autoload);
    }

    /**
     * @return string[]
     */
    private static function getWrongDirectoriesInPath(string $path): array
    {
        $globResult = self::glob_recursive($path, GLOB_ONLYDIR);

        return array_filter($globResult, function ($name) {
            return strpos($name, '/Wrong');
        });
    }

    /**
     * @see https://stackoverflow.com/a/12109100/1348344
     */
    private static function glob_recursive(string $pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::glob_recursive($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }
}
