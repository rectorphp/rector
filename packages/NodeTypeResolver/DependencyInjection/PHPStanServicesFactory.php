<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Factory so Symfony app can use services from PHPStan container
 * @see packages/NodeTypeResolver/config/config.yaml:17
 */
final class PHPStanServicesFactory
{
    /**
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;
    public function __construct(\RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $containerFactory = new \PHPStan\DependencyInjection\ContainerFactory(\getcwd());
        $additionalConfigFiles = [];
        $additionalConfigFiles[] = $parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH);
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';
        $existingAdditionalConfigFiles = \array_filter($additionalConfigFiles, 'file_exists');
        $this->container = $containerFactory->create(\sys_get_temp_dir(), $existingAdditionalConfigFiles, []);
    }
    /**
     * @api
     */
    public function createReflectionProvider() : \PHPStan\Reflection\ReflectionProvider
    {
        return $this->container->getByType(\PHPStan\Reflection\ReflectionProvider::class);
    }
    /**
     * @api
     */
    public function createNodeScopeResolver() : \PHPStan\Analyser\NodeScopeResolver
    {
        return $this->container->getByType(\PHPStan\Analyser\NodeScopeResolver::class);
    }
    /**
     * @api
     */
    public function createTypeSpecifier() : \PHPStan\Analyser\TypeSpecifier
    {
        return $this->container->getByType(\PHPStan\Analyser\TypeSpecifier::class);
    }
    /**
     * @api
     */
    public function createScopeFactory() : \PHPStan\Analyser\ScopeFactory
    {
        return $this->container->getByType(\PHPStan\Analyser\ScopeFactory::class);
    }
    /**
     * @api
     */
    public function createDependencyResolver() : \PHPStan\Dependency\DependencyResolver
    {
        return $this->container->getByType(\PHPStan\Dependency\DependencyResolver::class);
    }
    /**
     * @api
     */
    public function createFileHelper() : \PHPStan\File\FileHelper
    {
        return $this->container->getByType(\PHPStan\File\FileHelper::class);
    }
    /**
     * @api
     */
    public function createOperatorTypeSpecifyingExtensionRegistryProvider() : \PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider
    {
        return $this->container->getByType(\PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider::class);
    }
    /**
     * @api
     */
    public function createTypeNodeResolver() : \PHPStan\PhpDoc\TypeNodeResolver
    {
        return $this->container->getByType(\PHPStan\PhpDoc\TypeNodeResolver::class);
    }
    /**
     * @api
     */
    public function createDynamicSourceLocatorProvider() : \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
    {
        return $this->container->getByType(\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider::class);
    }
}
