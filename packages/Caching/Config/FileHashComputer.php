<?php

declare(strict_types=1);

namespace Rector\Caching\Config;

use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 * @see \Rector\Caching\Tests\Config\FileHashComputerTest
 */
final class FileHashComputer
{
    public function compute(SmartFileInfo $fileInfo): string
    {
        $this->ensureIsYamlOrPhp($fileInfo);

        $containerBuilder = new ContainerBuilder();
        $fileLoader = $this->createFileLoader($fileInfo, $containerBuilder);

        $fileLoader->load($fileInfo->getRealPath());

        $parameterBag = $containerBuilder->getParameterBag();
        return $this->arrayToHash($containerBuilder->getDefinitions()) . $this->arrayToHash($parameterBag->all());
    }

    private function ensureIsYamlOrPhp(SmartFileInfo $fileInfo): void
    {
        if ($fileInfo->hasSuffixes(['yml', 'yaml', 'php'])) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            // getRealPath() cannot be used, as it breaks in phar
            'Provide only YAML/PHP file, ready for Symfony Dependency Injection. "%s" given', $fileInfo->getRelativeFilePath()
        ));
    }

    private function createFileLoader(SmartFileInfo $fileInfo, ContainerBuilder $containerBuilder): LoaderInterface
    {
        $fileLocator = new FileLocator([$fileInfo->getPath()]);

        $fileLoaders = [
            new GlobFileLoader($containerBuilder, $fileLocator),
            new PhpFileLoader($containerBuilder, $fileLocator),
            new YamlFileLoader($containerBuilder, $fileLocator),
        ];

        $loaderResolver = new LoaderResolver($fileLoaders);
        $loader = $loaderResolver->resolve($fileInfo->getRealPath());
        if (! $loader) {
            throw new ShouldNotHappenException();
        }

        return $loader;
    }

    /**
     * @param mixed[] $array
     */
    private function arrayToHash(array $array): string
    {
        $serializedArray = serialize($array);
        return md5($serializedArray);
    }
}
