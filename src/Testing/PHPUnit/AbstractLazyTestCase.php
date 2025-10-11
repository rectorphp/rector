<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use Rector\Config\RectorConfig;
use Rector\DependencyInjection\LazyContainerFactory;
abstract class AbstractLazyTestCase extends TestCase
{
    protected static ?RectorConfig $rectorConfig = null;
    protected function setUp(): void
    {
        // this is needed to have always the same preloaded nikic/php-parser classes
        // in both bare AbstractLazyTestCase lazy tests and AbstractRectorTestCase tests
        $this->includePreloadFilesAndScoperAutoload();
    }
    /**
     * @api
     * @param string[] $configFiles
     */
    protected function bootFromConfigFiles(array $configFiles): void
    {
        $rectorConfig = self::getContainer();
        foreach ($configFiles as $configFile) {
            $rectorConfig->import($configFile);
        }
    }
    /**
     * @template TType as object
     * @param class-string<TType> $class
     * @return TType
     */
    protected function make(string $class): object
    {
        return self::getContainer()->make($class);
    }
    protected static function getContainer(): RectorConfig
    {
        if (!self::$rectorConfig instanceof RectorConfig) {
            $lazyContainerFactory = new LazyContainerFactory();
            self::$rectorConfig = $lazyContainerFactory->create();
        }
        self::$rectorConfig->boot();
        return self::$rectorConfig;
    }
    protected function isWindows(): bool
    {
        return strncasecmp(\PHP_OS, 'WIN', 3) === 0;
    }
    private function includePreloadFilesAndScoperAutoload(): void
    {
        if (file_exists(__DIR__ . '/../../../preload.php')) {
            if (file_exists(__DIR__ . '/../../../vendor')) {
                /**
                 * On PHPUnit 12+, when classmap autoloaded, it means preload already loaded early
                 */
                if (!class_exists(Version::class, \true) || (int) Version::id() < 12) {
                    require_once __DIR__ . '/../../../preload.php';
                }
                // test case in rector split package
            } elseif (file_exists(__DIR__ . '/../../../../../../vendor')) {
                require_once __DIR__ . '/../../../preload-split-package.php';
            }
        }
        if (\file_exists(__DIR__ . '/../../../vendor/scoper-autoload.php')) {
            require_once __DIR__ . '/../../../vendor/scoper-autoload.php';
        }
    }
}
