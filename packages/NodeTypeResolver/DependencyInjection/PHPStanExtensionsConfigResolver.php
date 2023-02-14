<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use PHPStan\ExtensionInstaller\GeneratedConfig;
use Rector\Core\Exception\ShouldNotHappenException;
use ReflectionClass;
/**
 * @see \Rector\Tests\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolverTest
 */
final class PHPStanExtensionsConfigResolver
{
    /**
     * @var string[]
     */
    private $cachedExtensionConfigFiles = [];
    /**
     * @return string[]
     */
    public function resolve() : array
    {
        // same logic as in PHPStan for extension installed - https://github.com/phpstan/phpstan-src/blob/5956ec4f6cd09c8d7db9466ed4e7f25706f37a43/src/Command/CommandHelper.php#L195-L222
        if (!\class_exists(GeneratedConfig::class)) {
            return [];
        }
        if ($this->cachedExtensionConfigFiles !== []) {
            return $this->cachedExtensionConfigFiles;
        }
        $reflectionClass = new ReflectionClass(GeneratedConfig::class);
        $generatedConfigClassFileName = $reflectionClass->getFileName();
        if ($generatedConfigClassFileName === \false) {
            throw new ShouldNotHappenException();
        }
        $generatedConfigDirectory = \dirname($generatedConfigClassFileName);
        $extensionConfigFiles = [];
        foreach (GeneratedConfig::EXTENSIONS as $extension) {
            $fileNames = $extension['extra']['includes'] ?? [];
            foreach ($fileNames as $fileName) {
                $configFilePath = $generatedConfigDirectory . '/' . $extension['relative_install_path'] . '/' . $fileName;
                $absoluteConfigFilePath = \realpath($configFilePath);
                if (!\is_string($absoluteConfigFilePath)) {
                    continue;
                }
                $extensionConfigFiles[] = $absoluteConfigFilePath;
            }
        }
        $this->cachedExtensionConfigFiles = $extensionConfigFiles;
        return $extensionConfigFiles;
    }
}
