<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\ExtensionInstaller\GeneratedConfig;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use ReflectionClass;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * Factory so Symfony app can use services from PHPStan container
 * @see packages/NodeTypeResolver/config/config.yaml:17
 */
final class PHPStanServicesFactory
{
    private Container $container;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $containerFactory = new ContainerFactory(getcwd());

        $additionalConfigFiles = [];

        $additionalConfigFiles[] = $parameterProvider->provideStringParameter(Option::PHPSTAN_FOR_RECTOR_PATH);
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';

        $extensionConfigFiles = $this->resolveExtensionConfigs();
        $additionalConfigFiles = array_merge($additionalConfigFiles, $extensionConfigFiles);

        $existingAdditionalConfigFiles = array_filter($additionalConfigFiles, 'file_exists');

        $this->container = $containerFactory->create(sys_get_temp_dir(), $existingAdditionalConfigFiles, []);
    }

    /**
     * @api
     */
    public function createReflectionProvider(): ReflectionProvider
    {
        return $this->container->getByType(ReflectionProvider::class);
    }

    /**
     * @api
     */
    public function createNodeScopeResolver(): NodeScopeResolver
    {
        return $this->container->getByType(NodeScopeResolver::class);
    }

    /**
     * @api
     */
    public function createScopeFactory(): ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }

    /**
     * @api
     */
    public function createDependencyResolver(): DependencyResolver
    {
        return $this->container->getByType(DependencyResolver::class);
    }

    /**
     * @api
     */
    public function createFileHelper(): FileHelper
    {
        return $this->container->getByType(FileHelper::class);
    }

    /**
     * @api
     */
    public function createTypeNodeResolver(): TypeNodeResolver
    {
        return $this->container->getByType(TypeNodeResolver::class);
    }

    /**
     * @api
     */
    public function createDynamicSourceLocatorProvider(): DynamicSourceLocatorProvider
    {
        return $this->container->getByType(DynamicSourceLocatorProvider::class);
    }

    /**
     * @return string[]
     */
    private function resolveExtensionConfigs(): array
    {
        // same logic as in PHPStan for extension installed - https://github.com/phpstan/phpstan-src/blob/5956ec4f6cd09c8d7db9466ed4e7f25706f37a43/src/Command/CommandHelper.php#L195-L222
        if (! class_exists('PHPStan\ExtensionInstaller\GeneratedConfig')) {
            return [];
        }

        $reflectionClass = new ReflectionClass(GeneratedConfig::class);
        $generatedConfigClassFileName = $reflectionClass->getFileName();
        if ($generatedConfigClassFileName === false) {
            throw new ShouldNotHappenException();
        }

        $generatedConfigDirectory = dirname($generatedConfigClassFileName);

        $extensionConfigFiles = [];

        foreach (GeneratedConfig::EXTENSIONS as $extension) {
            $fileNames = $extension['extra']['includes'] ?? [];
            foreach ($fileNames as $fileName) {
                $configFilePath = $generatedConfigDirectory . '/' . $extension['relative_install_path'] . '/' . $fileName;
                if (! file_exists($configFilePath)) {
                    continue;
                }

                $extensionConfigFiles[] = $configFilePath;
            }
        }

        return $extensionConfigFiles;
    }
}
