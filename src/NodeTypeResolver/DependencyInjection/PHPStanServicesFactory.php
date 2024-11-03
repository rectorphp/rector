<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix202411\Symfony\Component\Console\Input\ArrayInput;
use RectorPrefix202411\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202411\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * Factory so Symfony app can use services from PHPStan container
 *
 * @see \Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory
 */
final class PHPStanServicesFactory
{
    /**
     * @var string
     */
    private const INVALID_BLEEDING_EDGE_PATH_MESSAGE = <<<MESSAGE_ERROR
'%s, use full path bleedingEdge.neon config, eg:

includes:
    - phar://vendor/phpstan/phpstan/phpstan.phar/conf/bleedingEdge.neon

in your included phpstan configuration.

MESSAGE_ERROR;
    /**
     * @readonly
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;
    public function __construct()
    {
        $containerFactory = new ContainerFactory(\getcwd());
        $additionalConfigFiles = $this->resolveAdditionalConfigFiles();
        try {
            $this->container = $containerFactory->create(SimpleParameterProvider::provideStringParameter(Option::CONTAINER_CACHE_DIRECTORY), $additionalConfigFiles, []);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === "File 'phar://phpstan.phar/conf/bleedingEdge.neon' is missing or is not readable.") {
                $symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
                $symfonyStyle->error(\sprintf(self::INVALID_BLEEDING_EDGE_PATH_MESSAGE, $throwable->getMessage()));
                exit(-1);
            }
            throw $throwable;
        }
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
     * @template TObject as Object
     *
     * @param class-string<TObject> $type
     * @return TObject
     */
    public function getByType(string $type) : object
    {
        return $this->container->getByType($type);
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
        if (SimpleParameterProvider::hasParameter(Option::PHPSTAN_FOR_RECTOR_PATHS)) {
            $paths = SimpleParameterProvider::provideArrayParameter(Option::PHPSTAN_FOR_RECTOR_PATHS);
            foreach ($paths as $path) {
                Assert::string($path);
                $additionalConfigFiles[] = $path;
            }
        }
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/parser.neon';
        return \array_filter($additionalConfigFiles, \Closure::fromCallable('file_exists'));
    }
}
