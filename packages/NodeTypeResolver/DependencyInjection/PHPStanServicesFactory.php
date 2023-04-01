<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix202304\Symfony\Component\Filesystem\Filesystem;
/**
 * Factory so Symfony app can use services from PHPStan container
 *
 * @see \Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory
 */
final class PHPStanServicesFactory
{
    /**
     * @readonly
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolver
     */
    private $phpStanExtensionsConfigResolver;
    public function __construct(ParameterProvider $parameterProvider, \Rector\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolver $phpStanExtensionsConfigResolver, \Rector\NodeTypeResolver\DependencyInjection\BleedingEdgeIncludePurifier $bleedingEdgeIncludePurifier)
    {
        $this->parameterProvider = $parameterProvider;
        $this->phpStanExtensionsConfigResolver = $phpStanExtensionsConfigResolver;
        $additionalConfigFiles = $this->resolveAdditionalConfigFiles();
        $purifiedConfigFiles = [];
        foreach ($additionalConfigFiles as $key => $additionalConfigFile) {
            $purifiedConfigFile = $bleedingEdgeIncludePurifier->purifyConfigFile($additionalConfigFile);
            // nothing was changed
            if ($purifiedConfigFile === null) {
                continue;
            }
            $additionalConfigFiles[$key] = $purifiedConfigFile;
            $purifiedConfigFiles[] = $purifiedConfigFile;
        }
        $containerFactory = new ContainerFactory(\getcwd());
        $this->container = $containerFactory->create($parameterProvider->provideStringParameter(Option::CONTAINER_CACHE_DIRECTORY), $additionalConfigFiles, []);
        // clear temporary files, after container is created
        $filesystem = new Filesystem();
        $filesystem->remove($purifiedConfigFiles);
    }
    public function provideContainer() : Container
    {
        return $this->container;
    }
    /**
     * @api
     */
    public function createReflectionProvider() : ReflectionProvider
    {
        return $this->container->getByType(ReflectionProvider::class);
    }
    /**
     * @api
     */
    public function createEmulativeLexer() : Lexer
    {
        return $this->container->getService('currentPhpVersionLexer');
    }
    /**
     * @api
     */
    public function createPHPStanParser() : Parser
    {
        return $this->container->getService('currentPhpVersionRichParser');
    }
    /**
     * @api
     */
    public function createNodeScopeResolver() : NodeScopeResolver
    {
        return $this->container->getByType(NodeScopeResolver::class);
    }
    /**
     * @api
     */
    public function createScopeFactory() : ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }
    /**
     * @api
     */
    public function createDependencyResolver() : DependencyResolver
    {
        return $this->container->getByType(DependencyResolver::class);
    }
    /**
     * @api
     */
    public function createFileHelper() : FileHelper
    {
        return $this->container->getByType(FileHelper::class);
    }
    /**
     * @api
     */
    public function createTypeNodeResolver() : TypeNodeResolver
    {
        return $this->container->getByType(TypeNodeResolver::class);
    }
    /**
     * @api
     */
    public function createDynamicSourceLocatorProvider() : DynamicSourceLocatorProvider
    {
        return $this->container->getByType(DynamicSourceLocatorProvider::class);
    }
    /**
     * @return string[]
     */
    private function resolveAdditionalConfigFiles() : array
    {
        $additionalConfigFiles = [];
        if ($this->parameterProvider->hasParameter(Option::PHPSTAN_FOR_RECTOR_PATH)) {
            $additionalConfigFiles[] = $this->parameterProvider->provideStringParameter(Option::PHPSTAN_FOR_RECTOR_PATH);
        }
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/parser.neon';
        $extensionConfigFiles = $this->phpStanExtensionsConfigResolver->resolve();
        $additionalConfigFiles = \array_merge($additionalConfigFiles, $extensionConfigFiles);
        return \array_filter($additionalConfigFiles, 'file_exists');
    }
}
