<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Application\VersionResolver;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\FileHasher;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix202306\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202306\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix202306\Webmozart\Assert\Assert;
final class RectorKernel
{
    /**
     * @var string
     */
    private const CACHE_KEY = 'v68';
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    private $container = null;
    /**
     * @var bool
     */
    private $dumpFileCache = \false;
    /**
     * @var string|null
     */
    private static $defaultFilesHash;
    public function __construct()
    {
        // while running tests we use different DI containers a lot,
        // therefore make sure we don't compile them over and over again on rector-src.
        if (!StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }
        if ($this->isPrefixedBuild()) {
            return;
        }
        $this->dumpFileCache = \true;
    }
    /**
     * @param string[] $configFiles
     * @api used in tests
     */
    public function createBuilder(array $configFiles = []) : ContainerBuilder
    {
        return $this->buildContainer($configFiles);
    }
    /**
     * @param string[] $configFiles
     * @api used in tests
     */
    public function createFromConfigs(array $configFiles) : ContainerInterface
    {
        if ($configFiles === []) {
            return $this->buildContainer([]);
        }
        $container = $this->dumpFileCache ? $this->buildCachedContainer($configFiles) : $this->buildContainer($configFiles);
        return $this->container = $container;
    }
    /**
     * @api used in tests
     */
    public function getContainer() : ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new ShouldNotHappenException();
        }
        return $this->container;
    }
    public static function clearCache() : void
    {
        $cachedContainerBuilder = new \Rector\Core\Kernel\CachedContainerBuilder(self::getCacheDir(), self::CACHE_KEY);
        $cachedContainerBuilder->clearCache();
    }
    /**
     * @return string[]
     */
    private function createDefaultConfigFiles() : array
    {
        return [__DIR__ . '/../../config/config.php'];
    }
    /**
     * @param string[] $configFiles
     */
    private function createConfigsHash(array $configFiles) : string
    {
        $fileHasher = new FileHasher();
        if (self::$defaultFilesHash === null) {
            self::$defaultFilesHash = $fileHasher->hashFiles($this->createDefaultConfigFiles());
        }
        Assert::allString($configFiles);
        $configHash = $fileHasher->hashFiles($configFiles);
        return self::$defaultFilesHash . $configHash;
    }
    /**
     * @param string[] $configFiles
     */
    private function buildContainer(array $configFiles) : ContainerBuilder
    {
        $defaultConfigFiles = $this->createDefaultConfigFiles();
        $configFiles = \array_merge($defaultConfigFiles, $configFiles);
        $containerBuilderBuilder = new \Rector\Core\Kernel\ContainerBuilderBuilder();
        return $this->container = $containerBuilderBuilder->build($configFiles);
    }
    /**
     * @param string[] $configFiles
     */
    private function buildCachedContainer(array $configFiles) : ContainerInterface
    {
        $hash = $this->createConfigsHash($configFiles);
        $cachedContainerBuilder = new \Rector\Core\Kernel\CachedContainerBuilder(self::getCacheDir(), self::CACHE_KEY);
        return $cachedContainerBuilder->build($configFiles, $hash, function (array $configFiles) : ContainerBuilder {
            return $this->buildContainer($configFiles);
        });
    }
    private static function getCacheDir() : string
    {
        // we use the system temp dir only in our test-suite as we cannot reliably use it anywhere
        // see https://github.com/rectorphp/rector/issues/7700
        return \sys_get_temp_dir() . '/rector/';
    }
    private function isPrefixedBuild() : bool
    {
        return VersionResolver::PACKAGE_VERSION !== '@package_version@';
    }
}
