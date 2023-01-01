<?php

declare (strict_types=1);
namespace Rector\Caching\Config;

use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202301\Symfony\Component\Config\FileLocator;
use RectorPrefix202301\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202301\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix202301\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202301\Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use RectorPrefix202301\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 */
final class FileHashComputer
{
    public function compute(string $filePath) : string
    {
        $this->ensureIsPhp($filePath);
        $containerBuilder = new ContainerBuilder();
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
        throw new ShouldNotHappenException(\sprintf(
            // getRealPath() cannot be used, as it breaks in phar
            'Provide only PHP file, ready for Symfony Dependency Injection. "%s" given',
            $filePath
        ));
    }
    private function createFileLoader(string $filePath, ContainerBuilder $containerBuilder) : LoaderInterface
    {
        $fileLocator = new FileLocator([$filePath]);
        $fileLoaders = [new GlobFileLoader($containerBuilder, $fileLocator), new PhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new LoaderResolver($fileLoaders);
        $loader = $loaderResolver->resolve($filePath);
        if ($loader === \false) {
            throw new ShouldNotHappenException();
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
