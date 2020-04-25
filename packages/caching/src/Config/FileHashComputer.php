<?php

declare(strict_types=1);

namespace Rector\Caching\Config;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 */
final class FileHashComputer
{
    public function compute(string $filePath): string
    {
        $this->ensureIsYaml($filePath);

        $containerBuilder = new ContainerBuilder();

        $yamlFileLoader = new YamlFileLoader($containerBuilder, new FileLocator(dirname($filePath)));
        $yamlFileLoader->load($filePath);

        return $this->arrayToHash($containerBuilder->getDefinitions()) .
            $this->arrayToHash($containerBuilder->getParameterBag()->all());
    }

    /**
     * @param mixed[] $array
     */
    private function arrayToHash(array $array): string
    {
        return md5(serialize($array));
    }

    private function ensureIsYaml(string $filePath): void
    {
        if (Strings::match($filePath, '#\.(yml|yaml)$#')) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Provide only yml/yaml file, ready for Symfony DI. "%s" given', $filePath
        ));
    }
}
