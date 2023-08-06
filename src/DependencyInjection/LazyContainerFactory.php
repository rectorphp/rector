<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix202308\Doctrine\Inflector\Inflector;
use RectorPrefix202308\Doctrine\Inflector\Rules\English\InflectorFactory;
use RectorPrefix202308\Illuminate\Container\Container;
use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\Caching\Cache;
use Rector\Caching\CacheFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassConstFetchNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassConstNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\EmptyNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\FuncCallNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\FunctionNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\NameNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ParamNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\PropertyNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\UseNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\VariableNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ArgNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\AssignedToNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ByRefReturnNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ByRefVariableNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ContextNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\GlobalVariableNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\NameNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\StaticVariableNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\StmtKeyNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayItemNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ClassConstFetchAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ConstExprNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\CurlyListNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\DoctrineAnnotationAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\StringAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\StringNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use RectorPrefix202308\Symfony\Component\Console\Application;
final class LazyContainerFactory
{
    /**
     * @var array<class-string<NodeNameResolverInterface>>
     */
    private const NODE_NAME_RESOLVER_CLASSES = [ClassConstFetchNameResolver::class, ClassConstNameResolver::class, ClassNameResolver::class, EmptyNameResolver::class, FuncCallNameResolver::class, FunctionNameResolver::class, NameNameResolver::class, ParamNameResolver::class, PropertyNameResolver::class, UseNameResolver::class, VariableNameResolver::class];
    /**
     * @var array<class-string<AnnotationToAttributeMapperInterface>>
     */
    private const ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES = [ArrayAnnotationToAttributeMapper::class, ArrayItemNodeAnnotationToAttributeMapper::class, ClassConstFetchAnnotationToAttributeMapper::class, ConstExprNodeAnnotationToAttributeMapper::class, CurlyListNodeAnnotationToAttributeMapper::class, DoctrineAnnotationAnnotationToAttributeMapper::class, StringAnnotationToAttributeMapper::class, StringNodeAnnotationToAttributeMapper::class];
    /**
     * @var array<class-string<ScopeResolverNodeVisitorInterface>>
     */
    private const SCOPE_RESOLVER_NODE_VISITOR_CLASSES = [ArgNodeVisitor::class, AssignedToNodeVisitor::class, ByRefReturnNodeVisitor::class, ByRefVariableNodeVisitor::class, ContextNodeVisitor::class, GlobalVariableNodeVisitor::class, NameNodeVisitor::class, StaticVariableNodeVisitor::class, StmtKeyNodeVisitor::class];
    /**
     * @api used as next container factory
     */
    public function create() : Container
    {
        $container = new Container();
        // setup base parameters - from RectorConfig
        SimpleParameterProvider::setParameter(Option::CACHE_DIR, \sys_get_temp_dir() . '/rector_cached_files');
        SimpleParameterProvider::setParameter(Option::CONTAINER_CACHE_DIRECTORY, \sys_get_temp_dir());
        SimpleParameterProvider::setParameter(Option::INDENT_SIZE, 4);
        $container->singleton(Application::class, static function () : Application {
            $application = new Application();
            // @todo inject commands
            $privatesAccessor = new PrivatesAccessor();
            $privatesAccessor->propertyClosure($application, 'commands', static function (array $commands) : array {
                unset($commands['completion']);
                unset($commands['help']);
                return $commands;
            });
            return $application;
        });
        $container->singleton(Inflector::class, static function () : Inflector {
            $inflectorFactory = new InflectorFactory();
            return $inflectorFactory->build();
        });
        // caching
        $container->singleton(Cache::class, static function (Container $container) : Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $container->make(CacheFactory::class);
            return $cacheFactory->create();
        });
        // tagged services
        $container->when(BetterPhpDocParser::class)->needs('$phpDocNodeDecorators')->giveTagged(PhpDocNodeDecoratorInterface::class);
        $container->when(PHPStanStaticTypeMapper::class)->needs('$typeMappers')->giveTagged(TypeMapperInterface::class);
        $container->when(PhpDocTypeMapper::class)->needs('$phpDocTypeMappers')->giveTagged(PhpDocTypeMapperInterface::class);
        $container->when(PhpParserNodeMapper::class)->needs('$phpParserNodeMappers')->giveTagged(PhpParserNodeMapperInterface::class);
        $container->when(NodeTypeResolver::class)->needs('$nodeTypeResolvers')->giveTagged(NodeTypeResolverInterface::class);
        // node name resolvers
        $container->when(NodeNameResolver::class)->needs('$nodeNameResolvers')->giveTagged(NodeNameResolverInterface::class);
        $this->registerTagged($container, self::NODE_NAME_RESOLVER_CLASSES, NodeNameResolverInterface::class);
        $container->when(AnnotationToAttributeMapper::class)->needs('$annotationToAttributeMappers')->giveTagged(AnnotationToAttributeMapperInterface::class);
        $this->registerTagged($container, self::ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES, AnnotationToAttributeMapperInterface::class);
        // #[Required]-like setter
        $container->afterResolving(ArrayAnnotationToAttributeMapper::class, static function (ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributesMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayAnnotationToAttributeMapper->autowire($annotationToAttributesMapper);
        });
        $container->singleton(Parser::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createPHPStanParser();
        });
        $container->when(PHPStanNodeScopeResolver::class)->needs('$nodeVisitors')->giveTagged(ScopeResolverNodeVisitorInterface::class);
        $this->registerTagged($container, self::SCOPE_RESOLVER_NODE_VISITOR_CLASSES, ScopeResolverNodeVisitorInterface::class);
        // phpstan factory
        $this->createPhpstanServices($container);
        // @todo add base node visitors
        $container->when(PhpDocNodeMapper::class)->needs('$phpDocNodeVisitors')->giveTagged(BasePhpDocNodeVisitorInterface::class);
        return $container;
    }
    /**
     * @param array<class-string> $classes
     * @param class-string $tagInterface
     */
    private function registerTagged(Container $container, array $classes, string $tagInterface) : void
    {
        foreach ($classes as $class) {
            $container->singleton($class);
            $container->tag($class, $tagInterface);
        }
    }
    private function createPhpstanServices(Container $container) : void
    {
        $container->singleton(ReflectionProvider::class, static function (Container $container) : ReflectionProvider {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createReflectionProvider();
        });
        $container->singleton(Parser::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createPHPStanParser();
        });
        $container->singleton(Lexer::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createEmulativeLexer();
        });
        $container->singleton(TypeNodeResolver::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createTypeNodeResolver();
        });
        $container->singleton(NodeScopeResolver::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createNodeScopeResolver();
        });
        $container->singleton(FileHelper::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createFileHelper();
        });
        $container->singleton(DependencyResolver::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createDependencyResolver();
        });
        $container->singleton(ScopeFactory::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createScopeFactory();
        });
    }
}
