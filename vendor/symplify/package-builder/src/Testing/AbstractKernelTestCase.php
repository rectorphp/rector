<?php

declare (strict_types=1);
namespace RectorPrefix20211031\Symplify\PackageBuilder\Testing;

use RectorPrefix20211031\PHPUnit\Framework\TestCase;
use ReflectionClass;
use RectorPrefix20211031\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20211031\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211031\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface;
use RectorPrefix20211031\Symfony\Contracts\Service\ResetInterface;
use RectorPrefix20211031\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use RectorPrefix20211031\Symplify\PackageBuilder\Exception\HttpKernel\MissingInterfaceException;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211031\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
/**
 * Inspiration
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Test/KernelTestCase.php
 */
abstract class AbstractKernelTestCase extends \RectorPrefix20211031\PHPUnit\Framework\TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface|null
     */
    protected static $kernel;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    protected static $container;
    /**
     * @param class-string<KernelInterface> $kernelClass
     * @param string[]|SmartFileInfo[] $configs
     */
    protected function bootKernelWithConfigs($kernelClass, $configs) : \RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface
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
     * Syntax sugger to remove static from the test cases vission
     *
     * @template T of object
     * @param class-string<T> $type
     * @return object
     */
    protected function getService($type)
    {
        if (self::$container === null) {
            throw new \RectorPrefix20211031\Symplify\SymplifyKernel\Exception\ShouldNotHappenException('First, create container with booKernel(KernelClass::class)');
        }
        $service = self::$container->get($type);
        if ($service === null) {
            $errorMessage = \sprintf('Services "%s" was not found', $type);
            throw new \RectorPrefix20211031\Symplify\Astral\Exception\ShouldNotHappenException($errorMessage);
        }
        return $service;
    }
    /**
     * @param string $kernelClass
     */
    protected function bootKernel($kernelClass) : void
    {
        $this->ensureKernelShutdown();
        $kernel = new $kernelClass('test', \true);
        if (!$kernel instanceof \RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface) {
            throw new \RectorPrefix20211031\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
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
            $kernelReflectionClass = new \ReflectionClass(static::$kernel);
            $containerReflectionProperty = $kernelReflectionClass->getProperty('container');
            $containerReflectionProperty->setAccessible(\true);
            $kernel = $containerReflectionProperty->getValue(static::$kernel);
            if ($kernel !== null) {
                $container = static::$kernel->getContainer();
                static::$kernel->shutdown();
                if ($container instanceof \RectorPrefix20211031\Symfony\Contracts\Service\ResetInterface) {
                    $container->reset();
                }
            }
        }
        static::$container = null;
    }
    /**
     * @param string[] $configs
     */
    protected function resolveConfigsHash($configs) : string
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
    protected function resolveConfigFilePaths($configs) : array
    {
        $configFilePaths = [];
        foreach ($configs as $config) {
            $configFilePaths[] = $config instanceof \Symplify\SmartFileSystem\SmartFileInfo ? $config->getRealPath() : $config;
        }
        return $configFilePaths;
    }
    private function ensureIsConfigAwareKernel(\RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface $kernel) : void
    {
        if ($kernel instanceof \RectorPrefix20211031\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface) {
            return;
        }
        throw new \RectorPrefix20211031\Symplify\PackageBuilder\Exception\HttpKernel\MissingInterfaceException(\sprintf('"%s" is missing an "%s" interface', \get_class($kernel), \RectorPrefix20211031\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface::class));
    }
    private function bootAndReturnKernel(\RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface $kernel) : \RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface
    {
        $kernel->boot();
        $container = $kernel->getContainer();
        // private → public service hack?
        if ($container->has('test.service_container')) {
            $container = $container->get('test.service_container');
        }
        if (!$container instanceof \RectorPrefix20211031\Symfony\Component\DependencyInjection\ContainerInterface) {
            throw new \RectorPrefix20211031\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        // has output? keep it silent out of tests
        if ($container->has(\RectorPrefix20211031\Symfony\Component\Console\Style\SymfonyStyle::class)) {
            $symfonyStyle = $container->get(\RectorPrefix20211031\Symfony\Component\Console\Style\SymfonyStyle::class);
            $symfonyStyle->setVerbosity(\RectorPrefix20211031\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);
        }
        static::$container = $container;
        return $kernel;
    }
    /**
     * @param string[] $configFilePaths
     */
    private function createBootedKernelFromConfigs(string $kernelClass, string $configsHash, array $configFilePaths) : \RectorPrefix20211031\Symfony\Component\HttpKernel\KernelInterface
    {
        $kernel = new $kernelClass('test_' . $configsHash, \true);
        $this->ensureIsConfigAwareKernel($kernel);
        /** @var ExtraConfigAwareKernelInterface $kernel */
        $kernel->setConfigs($configFilePaths);
        return $this->bootAndReturnKernel($kernel);
    }
}
