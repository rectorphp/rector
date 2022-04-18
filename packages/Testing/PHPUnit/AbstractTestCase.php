<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use RectorPrefix20220418\Psr\Container\ContainerInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Kernel\RectorKernel;
use RectorPrefix20220418\Webmozart\Assert\Assert;
abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<string, RectorKernel>
     */
    private static $kernelsByHash = [];
    /**
     * @var \Psr\Container\ContainerInterface|null
     */
    private static $currentContainer;
    protected function boot() : void
    {
        $this->bootFromConfigFiles([]);
    }
    /**
     * @param string[] $configFiles
     */
    protected function bootFromConfigFiles(array $configFiles) : void
    {
        $configsHash = $this->createConfigsHash($configFiles);
        if (isset(self::$kernelsByHash[$configsHash])) {
            $rectorKernel = self::$kernelsByHash[$configsHash];
            self::$currentContainer = $rectorKernel->getContainer();
        } else {
            $rectorKernel = new \Rector\Core\Kernel\RectorKernel();
            $container = $rectorKernel->createFromConfigs($configFiles);
            self::$kernelsByHash[$configsHash] = $rectorKernel;
            self::$currentContainer = $container;
        }
    }
    /**
     * Syntax-sugar to remove static
     *
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    protected function getService(string $type) : object
    {
        if (self::$currentContainer === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException('First, create container with "bootWithConfigFileInfos([...])"');
        }
        $object = self::$currentContainer->get($type);
        if ($object === null) {
            $message = \sprintf('Service "%s" was not found', $type);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        return $object;
    }
    /**
     * @param string[] $configFiles
     */
    private function createConfigsHash(array $configFiles) : string
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allFile($configFiles);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allString($configFiles);
        $configHash = '';
        foreach ($configFiles as $configFile) {
            $configHash .= \md5_file($configFile);
        }
        return $configHash;
    }
}
