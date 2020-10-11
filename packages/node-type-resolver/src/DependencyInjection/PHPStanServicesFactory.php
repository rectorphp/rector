<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * Factory so Symfony app can use services from PHPStan container
 * @see packages/NodeTypeResolver/config/config.yaml:17
 */
final class PHPStanServicesFactory
{
    /**
     * @see https://regex101.com/r/CWADBe/2
     * @var string
     */
    private const BLEEDING_EDGE_REGEX = '#\n\s+-(.*?)bleedingEdge\.neon[\'|"]?#';

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $currentWorkingDirectory = getcwd();
        $smartFileSystem = new SmartFileSystem();

        $containerFactory = new ContainerFactory($currentWorkingDirectory);
        $additionalConfigFiles = [];

        // possible path collision for Docker
        $additionalConfigFiles = $this->appendPhpstanPHPUnitExtensionIfExists(
            $currentWorkingDirectory,
            $additionalConfigFiles
        );

        $temporaryPHPStanNeon = null;

        $currentProjectConfigFile = $currentWorkingDirectory . '/phpstan.neon';

        if (file_exists($currentProjectConfigFile)) {
            $phpstanNeonContent = $smartFileSystem->readFile($currentProjectConfigFile);

            // bleeding edge clean out, see https://github.com/rectorphp/rector/issues/2431
            if (Strings::match($phpstanNeonContent, self::BLEEDING_EDGE_REGEX)) {
                // Note: We need a unique file per process if rector runs in parallel
                $pid = getmypid();
                $temporaryPHPStanNeon = $currentWorkingDirectory . '/rector-temp-phpstan' . $pid . '.neon';
                $clearedPhpstanNeonContent = Strings::replace($phpstanNeonContent, self::BLEEDING_EDGE_REGEX);
                $smartFileSystem->dumpFile($temporaryPHPStanNeon, $clearedPhpstanNeonContent);

                $additionalConfigFiles[] = $temporaryPHPStanNeon;
            } else {
                $additionalConfigFiles[] = $currentProjectConfigFile;
            }
        }

        $additionalConfigFiles[] = __DIR__ . '/../../config/phpstan/type-extensions.neon';

        // enable type inferring from constructor
        $additionalConfigFiles[] = __DIR__ . '/../../config/phpstan/better-infer.neon';

        $this->container = $containerFactory->create(sys_get_temp_dir(), $additionalConfigFiles, []);

        // clear bleeding edge fallback
        if ($temporaryPHPStanNeon !== null) {
            $smartFileSystem->remove($temporaryPHPStanNeon);
        }
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
    public function createTypeSpecifier(): TypeSpecifier
    {
        return $this->container->getByType(TypeSpecifier::class);
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
    public function createDynamicReturnTypeExtensionRegistryProvider(): DynamicReturnTypeExtensionRegistryProvider
    {
        return $this->container->getByType(DynamicReturnTypeExtensionRegistryProvider::class);
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
    public function createOperatorTypeSpecifyingExtensionRegistryProvider(): OperatorTypeSpecifyingExtensionRegistryProvider
    {
        return $this->container->getByType(OperatorTypeSpecifyingExtensionRegistryProvider::class);
    }

    /**
     * @api
     */
    public function createTypeNodeResolver(): TypeNodeResolver
    {
        return $this->container->getByType(TypeNodeResolver::class);
    }

    /**
     * @param string[] $additionalConfigFiles
     * @return mixed[]
     */
    private function appendPhpstanPHPUnitExtensionIfExists(
        string $currentWorkingDirectory,
        array $additionalConfigFiles
    ): array {
        $phpstanPhpunitExtensionConfig = $currentWorkingDirectory . '/vendor/phpstan/phpstan-phpunit/extension.neon';
        if (file_exists($phpstanPhpunitExtensionConfig) && class_exists('PHPUnit\\Framework\\TestCase')) {
            $additionalConfigFiles[] = $phpstanPhpunitExtensionConfig;
        }
        return $additionalConfigFiles;
    }
}
