<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\PackageBuilder\Testing;

use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use ReflectionClass;
use RectorPrefix20210510\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210510\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Container;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\KernelInterface;
use RectorPrefix20210510\Symfony\Contracts\Service\ResetInterface;
use RectorPrefix20210510\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use RectorPrefix20210510\Symplify\PackageBuilder\Exception\HttpKernel\MissingInterfaceException;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210510\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
/**
 * Inspiration
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Test/KernelTestCase.php
 */
abstract class AbstractKernelTestCase extends TestCase
{
    /**
     * @var KernelInterface
     */
    protected static $kernel;
    /**
     * @var ContainerInterface|Container
     */
    protected static $container;
    /**
     * @var array<string, KernelInterface>
     */
    private static $kernelsByHash = [];
    /**
     * @param class-string<KernelInterface> $kernelClass
     * @param string[]|SmartFileInfo[] $configs
     */
    protected function bootKernelWithConfigs(string $kernelClass, array $configs) : KernelInterface
    {
        // unwrap file infos to real paths
        $configFilePaths = $this->resolveConfigFilePaths($configs);
        $configsHash = $this->resolveConfigsHash($configFilePaths);
        $this->ensureKernelShutdown();
        $bootedKernel = $this->createBootedKernelFromConfigs($kernelClass, $configsHash, $configFilePaths);
        static::$kernel = $bootedKernel;
        return $bootedKernel;
    }
    /**
     * @param class-string<KernelInterface> $kernelClass
     * @param string[]|SmartFileInfo[] $configs
     */
    protected function bootKernelWithConfigsAndStaticCache(string $kernelClass, array $configs) : KernelInterface
    {
        // unwrap file infos to real paths
        $configFilePaths = $this->resolveConfigFilePaths($configs);
        $configsHash = $this->resolveConfigsHash($configFilePaths);
        if (isset(self::$kernelsByHash[$configsHash])) {
            static::$kernel = self::$kernelsByHash[$configsHash];
            self::$container = static::$kernel->getContainer();
        } else {
            $bootedKernel = $this->createBootedKernelFromConfigs($kernelClass, $configsHash, $configFilePaths);
            static::$kernel = $bootedKernel;
            self::$kernelsByHash[$configsHash] = $bootedKernel;
        }
        return static::$kernel;
    }
    /**
     * Syntax sugger to remove static from the test cases vission
     *
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    protected function getService(string $type) : object
    {
        if (self::$container === null) {
            throw new ShouldNotHappenException('First, crewate container with booKernel(KernelClass::class)');
        }
        return self::$container->get($type);
    }
    protected function bootKernel(string $kernelClass) : void
    {
        $this->ensureKernelShutdown();
        $kernel = new $kernelClass('test', \true);
        if (!$kernel instanceof KernelInterface) {
            throw new ShouldNotHappenException();
        }
        static::$kernel = $this->bootAndReturnKernel($kernel);
    }
    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected function ensureKernelShutdown() : void
    {
        if (static::$kernel !== null) {
            // make sure boot() is called
            // @see https://github.com/symfony/symfony/pull/31202/files
            $kernelReflectionClass = new ReflectionClass(static::$kernel);
            $containerReflectionProperty = $kernelReflectionClass->getProperty('container');
            $containerReflectionProperty->setAccessible(\true);
            $kernel = $containerReflectionProperty->getValue(static::$kernel);
            if ($kernel !== null) {
                $container = static::$kernel->getContainer();
                static::$kernel->shutdown();
                if ($container instanceof ResetInterface) {
                    $container->reset();
                }
            }
        }
        static::$container = null;
    }
    /**
     * @param string[] $configs
     */
    protected function resolveConfigsHash(array $configs) : string
    {
        $configsHash = '';
        foreach ($configs as $config) {
            $configsHash .= \md5_file($config);
        }
        return \md5($configsHash);
    }
    /**
     * @param string[]|SmartFileInfo[] $configs
     * @return string[]
     */
    protected function resolveConfigFilePaths(array $configs) : array
    {
        $configFilePaths = [];
        foreach ($configs as $config) {
            $configFilePaths[] = $config instanceof SmartFileInfo ? $config->getRealPath() : $config;
        }
        return $configFilePaths;
    }
    private function ensureIsConfigAwareKernel(KernelInterface $kernel) : void
    {
        if ($kernel instanceof ExtraConfigAwareKernelInterface) {
            return;
        }
        throw new MissingInterfaceException(\sprintf('"%s" is missing an "%s" interface', \get_class($kernel), ExtraConfigAwareKernelInterface::class));
    }
    private function bootAndReturnKernel(KernelInterface $kernel) : KernelInterface
    {
        $kernel->boot();
        $container = $kernel->getContainer();
        // private → public service hack?
        if ($container->has('test.service_container')) {
            $container = $container->get('test.service_container');
        }
        if (!$container instanceof ContainerInterface) {
            throw new ShouldNotHappenException();
        }
        // has output? keep it silent out of tests
        if ($container->has(SymfonyStyle::class)) {
            $symfonyStyle = $container->get(SymfonyStyle::class);
            $symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        static::$container = $container;
        return $kernel;
    }
    /**
     * @param string[] $configFilePaths
     */
    private function createBootedKernelFromConfigs(string $kernelClass, string $configsHash, array $configFilePaths) : KernelInterface
    {
        $kernel = new $kernelClass('test_' . $configsHash, \true);
        $this->ensureIsConfigAwareKernel($kernel);
        /** @var ExtraConfigAwareKernelInterface $kernel */
        $kernel->setConfigs($configFilePaths);
        return $this->bootAndReturnKernel($kernel);
    }
}
