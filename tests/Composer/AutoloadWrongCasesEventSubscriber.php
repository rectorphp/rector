<?php declare(strict_types=1);

namespace Rector\Tests\Composer;

use Composer\Script\Event;

final class AutoloadWrongCasesEventSubscriber
{
    /**
     * @var string[]
     */
    private static $wrongDirectories = [];

    /**
     * @see https://www.drupal.org/files/issues/vendor-classmap-2468499-14.patch
     */
    public static function preAutoloadDump(Event $event): void
    {
        return;

        $paths = [
            __DIR__ . '/../../tests/Rector/*',
            __DIR__ . '/../../tests/Issues/*',
            __DIR__ . '/../../packages/*',
        ];

        foreach ($paths as $path) {
            self::$wrongDirectories = array_merge(self::$wrongDirectories, self::getWrongDirectoriesInPath($path));
        }

        sort(self::$wrongDirectories);

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
        $globResult = self::globRecursive($path, GLOB_ONLYDIR);

        return array_filter($globResult, function ($name) {
            return strpos($name, '/Wrong') && ! strpos($name, 'ContributorTools');
        });
    }

    /**
     * @see https://stackoverflow.com/a/12109100/1348344
     * @return string[]
     */
    private static function globRecursive(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::globRecursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}
