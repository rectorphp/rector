<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;

final class PHPStanServicesFactory
{
    /**
     * @see https://regex101.com/r/CWADBe/2
     * @var string
     */
    private const BLEEDING_EDGE_PATTERN = '#\n\s+-(.*?)bleedingEdge\.neon[\'|"]?#';

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $currentWorkingDirectory = getcwd();

        $containerFactory = new ContainerFactory($currentWorkingDirectory);
        $additionalConfigFiles = [];

        // possible path collision for Docker
        $phpstanPhpunitExtensionConfig = $currentWorkingDirectory . '/vendor/phpstan/phpstan-phpunit/extension.neon';
        if (file_exists($phpstanPhpunitExtensionConfig) && class_exists('PHPUnit\\Framework\\TestCase')) {
            $additionalConfigFiles[] = $phpstanPhpunitExtensionConfig;
        }

        $temporaryPhpstanNeon = null;

        $currentProjectConfigFile = $currentWorkingDirectory . '/phpstan.neon';
        if (file_exists($currentProjectConfigFile)) {
            $phpstanNeonContent = FileSystem::read($currentProjectConfigFile);

            // bleeding edge clean out, see https://github.com/rectorphp/rector/issues/2431
            if (Strings::match($phpstanNeonContent, self::BLEEDING_EDGE_PATTERN)) {
                $temporaryPhpstanNeon = $currentWorkingDirectory . '/rector-temp-phpstan.neon';
                $clearedPhpstanNeonContent = Strings::replace($phpstanNeonContent, self::BLEEDING_EDGE_PATTERN);
                FileSystem::write($temporaryPhpstanNeon, $clearedPhpstanNeonContent);

                $additionalConfigFiles[] = $temporaryPhpstanNeon;
            } else {
                $additionalConfigFiles[] = $currentProjectConfigFile;
            }
        }

        $additionalConfigFiles[] = __DIR__ . '/../../config/phpstan/type-extensions.neon';

        // enable type inferring from constructor
        $additionalConfigFiles[] = __DIR__ . '/../../config/phpstan/better-infer.neon';

        $this->container = $containerFactory->create(sys_get_temp_dir(), $additionalConfigFiles, []);

        // clear bleeding edge fallback
        if ($temporaryPhpstanNeon !== null) {
            FileSystem::delete($temporaryPhpstanNeon);
        }
    }

    public function createBroker(): Broker
    {
        return $this->container->getByType(Broker::class);
    }

    public function createNodeScopeResolver(): NodeScopeResolver
    {
        return $this->container->getByType(NodeScopeResolver::class);
    }

    public function createTypeSpecifier(): TypeSpecifier
    {
        return $this->container->getByType(TypeSpecifier::class);
    }

    public function createScopeFactory(): ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }

    public function createDynamicReturnTypeExtensionRegistryProvider(): DynamicReturnTypeExtensionRegistryProvider
    {
        return $this->container->getByType(DynamicReturnTypeExtensionRegistryProvider::class);
    }

    public function createOperatorTypeSpecifyingExtensionRegistryProvider(): OperatorTypeSpecifyingExtensionRegistryProvider
    {
        return $this->container->getByType(OperatorTypeSpecifyingExtensionRegistryProvider::class);
    }
}
