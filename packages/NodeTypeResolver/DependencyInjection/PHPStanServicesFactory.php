<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\ExtensionInstaller\GeneratedConfig;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use ReflectionClass;
use RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Factory so Symfony app can use services from PHPStan container
 */
final class PHPStanServicesFactory
{
    /**
     * @readonly
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;
    public function __construct(\RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $containerFactory = new \PHPStan\DependencyInjection\ContainerFactory(\getcwd());
        $additionalConfigFiles = [];
        if ($parameterProvider->hasParameter(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH)) {
            $additionalConfigFiles[] = $parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH);
        }
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/parser.neon';
        $extensionConfigFiles = $this->resolveExtensionConfigs();
        $additionalConfigFiles = \array_merge($additionalConfigFiles, $extensionConfigFiles);
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
    public function createEmulativeLexer() : \PhpParser\Lexer
    {
        return $this->container->getService('currentPhpVersionLexer');
    }
    /**
     * @api
     */
    public function createPHPStanParser() : \PHPStan\Parser\Parser
    {
        return $this->container->getService('currentPhpVersionRichParser');
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
    /**
     * @return string[]
     */
    private function resolveExtensionConfigs() : array
    {
        // same logic as in PHPStan for extension installed - https://github.com/phpstan/phpstan-src/blob/5956ec4f6cd09c8d7db9466ed4e7f25706f37a43/src/Command/CommandHelper.php#L195-L222
        if (!\class_exists(\PHPStan\ExtensionInstaller\GeneratedConfig::class)) {
            return [];
        }
        $reflectionClass = new \ReflectionClass(\PHPStan\ExtensionInstaller\GeneratedConfig::class);
        $generatedConfigClassFileName = $reflectionClass->getFileName();
        if ($generatedConfigClassFileName === \false) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $generatedConfigDirectory = \dirname($generatedConfigClassFileName);
        $extensionConfigFiles = [];
        foreach (\PHPStan\ExtensionInstaller\GeneratedConfig::EXTENSIONS as $extension) {
            $fileNames = $extension['extra']['includes'] ?? [];
            foreach ($fileNames as $fileName) {
                $configFilePath = $generatedConfigDirectory . '/' . $extension['relative_install_path'] . '/' . $fileName;
                if (!\file_exists($configFilePath)) {
                    continue;
                }
                $extensionConfigFiles[] = $configFilePath;
            }
        }
        return $extensionConfigFiles;
    }
}
