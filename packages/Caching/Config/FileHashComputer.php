<?php

declare (strict_types=1);
namespace Rector\Caching\Config;

use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220418\Symfony\Component\Config\FileLocator;
use RectorPrefix20220418\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220418\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 */
final class FileHashComputer
{
    public function compute(string $filePath) : string
    {
        $this->ensureIsPhp($filePath);
        $containerBuilder = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder();
        $fileLoader = $this->createFileLoader($filePath, $containerBuilder);
        $fileLoader->load($filePath);
        $parameterBag = $containerBuilder->getParameterBag();
        return $this->arrayToHash($containerBuilder->getDefinitions()) . $this->arrayToHash($parameterBag->all());
    }
    private function ensureIsPhp(string $filePath) : void
    {
        $fileExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
        if ($fileExtension === 'php') {
            return;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf(
            // getRealPath() cannot be used, as it breaks in phar
            'Provide only PHP file, ready for Symfony Dependency Injection. "%s" given',
            $filePath
        ));
    }
    private function createFileLoader(string $filePath, \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : \RectorPrefix20220418\Symfony\Component\Config\Loader\LoaderInterface
    {
        $fileLocator = new \RectorPrefix20220418\Symfony\Component\Config\FileLocator([$filePath]);
        $fileLoaders = [new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\GlobFileLoader($containerBuilder, $fileLocator), new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new \RectorPrefix20220418\Symfony\Component\Config\Loader\LoaderResolver($fileLoaders);
        $loader = $loaderResolver->resolve($filePath);
        if ($loader === \false) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $loader;
    }
    /**
     * @param mixed[] $array
     */
    private function arrayToHash(array $array) : string
    {
        $serializedArray = \serialize($array);
        return \md5($serializedArray);
    }
}
