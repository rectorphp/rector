<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix202308\Doctrine\Inflector\Inflector;
use RectorPrefix202308\Doctrine\Inflector\Rules\English\InflectorFactory;
use RectorPrefix202308\Illuminate\Container\Container;
use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ArrayTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\CallableTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\IntersectionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\TemplatePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\UnionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\PhpDocParser\BetterTypeParser;
use Rector\BetterPhpDocParser\PhpDocParser\ConstExprClassNameDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Rector\Caching\Cache;
use Rector\Caching\CacheFactory;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\AliasClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ClassLikeNameClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\FullyQualifiedNameClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\UsesClassNameImportSkipVoter;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Config\LazyRectorConfig;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\ChangedNodeScopeRefresher;
use Rector\Core\Application\FileProcessor\PhpFileProcessor;
use Rector\Core\Configuration\ConfigInitializer;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Console\Command\ListRulesCommand;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\Console\Command\SetupCICommand;
use Rector\Core\Console\Command\WorkerCommand;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Console\Style\RectorStyle;
use Rector\Core\Console\Style\SymfonyStyleFactory;
use Rector\Core\Contract\DependencyInjection\ResetableInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Logging\RectorOutput;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassConstFetchNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassConstNameResolver;
use Rector\NodeNameResolver\NodeNameResolver\ClassNameResolver;
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
use Rector\NodeTypeResolver\NodeTypeResolver\CastTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ClassAndInterfaceTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ClassMethodOrClassConstTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\NameTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\NewTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ParamTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\PropertyTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ReturnTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ScalarTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\StaticCallMethodCallTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\TraitTypeResolver;
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
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
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
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryLiteralStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNonEmptyStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNonFalsyStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNumericStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\BooleanTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\CallableTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ClassStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ClosureTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeForParameterMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\FloatTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\GenericClassStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasMethodTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasOffsetTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasOffsetValueTypeTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasPropertyTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\IntegerTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\IterableTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\MixedTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\NeverTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\NonEmptyArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\NullTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\OversizedArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ParentStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ResourceTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\SelfObjectTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StrictMixedTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ThisTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\TypeWithClassNameTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\VoidTypeMapper;
use Rector\Skipper\Skipper\Skipper;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\IdentifierTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\IntersectionTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\NullableTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\UnionTypeMapper;
use Rector\StaticTypeMapper\PhpParser\ExprNodeMapper;
use Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper;
use Rector\StaticTypeMapper\PhpParser\IntersectionTypeNodeMapper;
use Rector\StaticTypeMapper\PhpParser\NameNodeMapper;
use Rector\StaticTypeMapper\PhpParser\NullableTypeNodeMapper;
use Rector\StaticTypeMapper\PhpParser\StringNodeMapper;
use Rector\StaticTypeMapper\PhpParser\UnionTypeNodeMapper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Utils\Command\MissingInSetCommand;
use Rector\Utils\Command\OutsideAnySetCommand;
use RectorPrefix202308\Symfony\Component\Console\Application;
use RectorPrefix202308\Symfony\Component\Console\Command\Command;
use RectorPrefix202308\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202308\Webmozart\Assert\Assert;
final class LazyContainerFactory
{
    /**
     * @var array<class-string<NodeNameResolverInterface>>
     */
    private const NODE_NAME_RESOLVER_CLASSES = [ClassConstFetchNameResolver::class, ClassConstNameResolver::class, ClassNameResolver::class, FuncCallNameResolver::class, FunctionNameResolver::class, NameNameResolver::class, ParamNameResolver::class, PropertyNameResolver::class, UseNameResolver::class, VariableNameResolver::class];
    /**
     * @var array<class-string<BasePhpDocNodeVisitorInterface>>
     */
    private const BASE_PHP_DOC_NODE_VISITORS = [ArrayTypePhpDocNodeVisitor::class, CallableTypePhpDocNodeVisitor::class, IntersectionTypeNodePhpDocNodeVisitor::class, TemplatePhpDocNodeVisitor::class, UnionTypeNodePhpDocNodeVisitor::class];
    /**
     * @var array<class-string<AnnotationToAttributeMapperInterface>>
     */
    private const ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES = [ArrayAnnotationToAttributeMapper::class, ArrayItemNodeAnnotationToAttributeMapper::class, ClassConstFetchAnnotationToAttributeMapper::class, ConstExprNodeAnnotationToAttributeMapper::class, CurlyListNodeAnnotationToAttributeMapper::class, DoctrineAnnotationAnnotationToAttributeMapper::class, StringAnnotationToAttributeMapper::class, StringNodeAnnotationToAttributeMapper::class];
    /**
     * @var array<class-string<ScopeResolverNodeVisitorInterface>>
     */
    private const SCOPE_RESOLVER_NODE_VISITOR_CLASSES = [ArgNodeVisitor::class, AssignedToNodeVisitor::class, ByRefReturnNodeVisitor::class, ByRefVariableNodeVisitor::class, ContextNodeVisitor::class, GlobalVariableNodeVisitor::class, NameNodeVisitor::class, StaticVariableNodeVisitor::class, StmtKeyNodeVisitor::class];
    /**
     * @var array<class-string<PhpDocTypeMapperInterface>>
     */
    private const PHPDOC_TYPE_MAPPER_CLASSES = [IdentifierTypeMapper::class, IntersectionTypeMapper::class, NullableTypeMapper::class, UnionTypeMapper::class];
    /**
     * @var array<class-string<ClassNameImportSkipVoterInterface>>
     */
    private const CLASS_NAME_IMPORT_SKIPPER_CLASSES = [AliasClassNameImportSkipVoter::class, ClassLikeNameClassNameImportSkipVoter::class, FullyQualifiedNameClassNameImportSkipVoter::class, UsesClassNameImportSkipVoter::class];
    /**
     * @var array<class-string<TypeMapperInterface>>
     */
    private const TYPE_MAPPER_CLASSES = [AccessoryLiteralStringTypeMapper::class, AccessoryNonEmptyStringTypeMapper::class, AccessoryNonFalsyStringTypeMapper::class, AccessoryNumericStringTypeMapper::class, ArrayTypeMapper::class, BooleanTypeMapper::class, CallableTypeMapper::class, ClassStringTypeMapper::class, ClosureTypeMapper::class, ConditionalTypeForParameterMapper::class, ConditionalTypeMapper::class, FloatTypeMapper::class, GenericClassStringTypeMapper::class, HasMethodTypeMapper::class, HasOffsetTypeMapper::class, HasOffsetValueTypeTypeMapper::class, HasPropertyTypeMapper::class, IntegerTypeMapper::class, \Rector\PHPStanStaticTypeMapper\TypeMapper\IntersectionTypeMapper::class, IterableTypeMapper::class, MixedTypeMapper::class, NeverTypeMapper::class, NonEmptyArrayTypeMapper::class, NullTypeMapper::class, ObjectTypeMapper::class, ObjectWithoutClassTypeMapper::class, OversizedArrayTypeMapper::class, ParentStaticTypeMapper::class, ResourceTypeMapper::class, SelfObjectTypeMapper::class, \Rector\PHPStanStaticTypeMapper\TypeMapper\StaticTypeMapper::class, StrictMixedTypeMapper::class, StringTypeMapper::class, ThisTypeMapper::class, TypeWithClassNameTypeMapper::class, \Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper::class, VoidTypeMapper::class];
    /**
     * @var array<class-string<PhpDocNodeDecoratorInterface>>
     */
    private const PHP_DOC_NODE_DECORATOR_CLASSES = [ConstExprClassNameDecorator::class, DoctrineAnnotationDecorator::class];
    /**
     * @var array<class-string>
     */
    private const PUBLIC_PHPSTAN_SERVICE_TYPES = [ScopeFactory::class, TypeNodeResolver::class, FileHelper::class, NodeScopeResolver::class, ReflectionProvider::class];
    /**
     * @var array<class-string<OutputFormatterInterface>>
     */
    private const OUTPUT_FORMATTER_CLASSES = [ConsoleOutputFormatter::class, JsonOutputFormatter::class];
    /**
     * @var array<class-string<NodeTypeResolverInterface>>
     */
    private const NODE_TYPE_RESOLVER_CLASSES = [CastTypeResolver::class, ClassAndInterfaceTypeResolver::class, ClassMethodOrClassConstTypeResolver::class, IdentifierTypeResolver::class, NameTypeResolver::class, NewTypeResolver::class, ParamTypeResolver::class, PropertyFetchTypeResolver::class, PropertyTypeResolver::class, ReturnTypeResolver::class, ScalarTypeResolver::class, StaticCallMethodCallTypeResolver::class, TraitTypeResolver::class];
    /**
     * @var array<class-string<PhpParserNodeMapperInterface>>
     */
    private const PHP_PARSER_NODE_MAPPER_CLASSES = [ExprNodeMapper::class, FullyQualifiedNodeMapper::class, IdentifierNodeMapper::class, IntersectionTypeNodeMapper::class, NameNodeMapper::class, NullableTypeNodeMapper::class, StringNodeMapper::class, UnionTypeNodeMapper::class];
    /**
     * @api used as next container factory
     */
    public function create() : LazyRectorConfig
    {
        $lazyRectorConfig = new LazyRectorConfig();
        // setup base parameters - from RectorConfig
        // make use of https://github.com/symplify/easy-parallel
        // $lazyRectorConfig->import(EasyParallelConfig::FILE_PATH);
        $lazyRectorConfig->paths([]);
        $lazyRectorConfig->skip([]);
        $lazyRectorConfig->autoloadPaths([]);
        $lazyRectorConfig->bootstrapFiles([]);
        $lazyRectorConfig->parallel(120, 16, 20);
        // to avoid autoimporting out of the box
        $lazyRectorConfig->importNames(\false, \false);
        $lazyRectorConfig->removeUnusedImports(\false);
        $lazyRectorConfig->importShortClasses();
        $lazyRectorConfig->indent(' ', 4);
        $lazyRectorConfig->fileExtensions(['php']);
        $lazyRectorConfig->cacheDirectory(\sys_get_temp_dir() . '/rector_cached_files');
        $lazyRectorConfig->containerCacheDirectory(\sys_get_temp_dir());
        // make use of https://github.com/symplify/easy-parallel
        //        $lazyRectorConfig->import(EasyParallelConfig::FILE_PATH);
        $lazyRectorConfig->singleton(Application::class, static function () : Application {
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
        $lazyRectorConfig->singleton(Inflector::class, static function () : Inflector {
            $inflectorFactory = new InflectorFactory();
            return $inflectorFactory->build();
        });
        $lazyRectorConfig->singleton(ConsoleApplication::class, ConsoleApplication::class);
        $lazyRectorConfig->when(ConsoleApplication::class)->needs('$commands')->giveTagged(Command::class);
        $lazyRectorConfig->tag(PhpFileProcessor::class, FileProcessorInterface::class);
        $lazyRectorConfig->tag(ProcessCommand::class, Command::class);
        $lazyRectorConfig->tag(WorkerCommand::class, Command::class);
        $lazyRectorConfig->tag(SetupCICommand::class, Command::class);
        $lazyRectorConfig->tag(ListRulesCommand::class, Command::class);
        $lazyRectorConfig->when(ListRulesCommand::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $lazyRectorConfig->alias(TypeParser::class, BetterTypeParser::class);
        // dev
        $lazyRectorConfig->tag(MissingInSetCommand::class, Command::class);
        $lazyRectorConfig->tag(OutsideAnySetCommand::class, Command::class);
        $lazyRectorConfig->when(ApplicationFileProcessor::class)->needs('$fileProcessors')->giveTagged(FileProcessorInterface::class);
        $lazyRectorConfig->when(FileFactory::class)->needs('$fileProcessors')->giveTagged(FileProcessorInterface::class);
        $lazyRectorConfig->when(RectorNodeTraverser::class)->needs('$phpRectors')->giveTagged(PhpRectorInterface::class);
        $lazyRectorConfig->when(ConfigInitializer::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $lazyRectorConfig->when(ClassNameImportSkipper::class)->needs('$classNameImportSkipVoters')->giveTagged(ClassNameImportSkipVoterInterface::class);
        $lazyRectorConfig->singleton(DynamicSourceLocatorProvider::class, static function (Container $container) : DynamicSourceLocatorProvider {
            $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createDynamicSourceLocatorProvider();
        });
        // resetables
        $lazyRectorConfig->tag(DynamicSourceLocatorProvider::class, ResetableInterface::class);
        $lazyRectorConfig->tag(RenamedClassesDataCollector::class, ResetableInterface::class);
        // caching
        $lazyRectorConfig->singleton(Cache::class, static function (Container $container) : Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $container->make(CacheFactory::class);
            return $cacheFactory->create();
        });
        // tagged services
        $lazyRectorConfig->when(BetterPhpDocParser::class)->needs('$phpDocNodeDecorators')->giveTagged(PhpDocNodeDecoratorInterface::class);
        $lazyRectorConfig->afterResolving(ConditionalTypeForParameterMapper::class, static function (ConditionalTypeForParameterMapper $conditionalTypeForParameterMapper, Container $container) : void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeForParameterMapper->autowire($phpStanStaticTypeMapper);
        });
        $lazyRectorConfig->when(PHPStanStaticTypeMapper::class)->needs('$typeMappers')->giveTagged(TypeMapperInterface::class);
        $lazyRectorConfig->when(PhpDocTypeMapper::class)->needs('$phpDocTypeMappers')->giveTagged(PhpDocTypeMapperInterface::class);
        $lazyRectorConfig->when(PhpParserNodeMapper::class)->needs('$phpParserNodeMappers')->giveTagged(PhpParserNodeMapperInterface::class);
        $lazyRectorConfig->when(NodeTypeResolver::class)->needs('$nodeTypeResolvers')->giveTagged(NodeTypeResolverInterface::class);
        // node name resolvers
        $lazyRectorConfig->when(NodeNameResolver::class)->needs('$nodeNameResolvers')->giveTagged(NodeNameResolverInterface::class);
        $lazyRectorConfig->afterResolving(AbstractRector::class, static function (AbstractRector $rector, Container $container) : void {
            $rector->autowire($container->make(NodeNameResolver::class), $container->make(NodeTypeResolver::class), $container->make(SimpleCallableNodeTraverser::class), $container->make(NodeFactory::class), $container->make(PhpDocInfoFactory::class), $container->make(StaticTypeMapper::class), $container->make(CurrentRectorProvider::class), $container->make(CurrentNodeProvider::class), $container->make(Skipper::class), $container->make(ValueResolver::class), $container->make(BetterNodeFinder::class), $container->make(NodeComparator::class), $container->make(CurrentFileProvider::class), $container->make(CreatedByRuleDecorator::class), $container->make(ChangedNodeScopeRefresher::class), $container->make(RectorOutput::class));
        });
        $this->registerTagged($lazyRectorConfig, self::PHP_PARSER_NODE_MAPPER_CLASSES, PhpParserNodeMapperInterface::class);
        $this->registerTagged($lazyRectorConfig, self::PHP_DOC_NODE_DECORATOR_CLASSES, PhpDocNodeDecoratorInterface::class);
        $this->registerTagged($lazyRectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
        $this->registerTagged($lazyRectorConfig, self::TYPE_MAPPER_CLASSES, TypeMapperInterface::class);
        $this->registerTagged($lazyRectorConfig, self::PHPDOC_TYPE_MAPPER_CLASSES, PhpDocTypeMapperInterface::class);
        $this->registerTagged($lazyRectorConfig, self::NODE_NAME_RESOLVER_CLASSES, NodeNameResolverInterface::class);
        $this->registerTagged($lazyRectorConfig, self::NODE_TYPE_RESOLVER_CLASSES, NodeTypeResolverInterface::class);
        $this->registerTagged($lazyRectorConfig, self::OUTPUT_FORMATTER_CLASSES, OutputFormatterInterface::class);
        $this->registerTagged($lazyRectorConfig, self::CLASS_NAME_IMPORT_SKIPPER_CLASSES, ClassNameImportSkipVoterInterface::class);
        $lazyRectorConfig->alias(SymfonyStyle::class, RectorStyle::class);
        $lazyRectorConfig->singleton(SymfonyStyle::class, static function (Container $container) : SymfonyStyle {
            $symfonyStyleFactory = $container->make(SymfonyStyleFactory::class);
            return $symfonyStyleFactory->create();
        });
        $this->registerTagged($lazyRectorConfig, self::ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES, AnnotationToAttributeMapperInterface::class);
        $lazyRectorConfig->when(AnnotationToAttributeMapper::class)->needs('$annotationToAttributeMappers')->giveTagged(AnnotationToAttributeMapperInterface::class);
        $lazyRectorConfig->when(OutputFormatterCollector::class)->needs('$outputFormatters')->giveTagged(OutputFormatterInterface::class);
        // #[Required]-like setter
        $lazyRectorConfig->afterResolving(ArrayAnnotationToAttributeMapper::class, static function (ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributesMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayAnnotationToAttributeMapper->autowire($annotationToAttributesMapper);
        });
        $lazyRectorConfig->afterResolving(ArrayItemNodeAnnotationToAttributeMapper::class, static function (ArrayItemNodeAnnotationToAttributeMapper $arrayItemNodeAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayItemNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $lazyRectorConfig->afterResolving(NameScopeFactory::class, static function (NameScopeFactory $nameScopeFactory, Container $container) : void {
            $nameScopeFactory->autowire($container->make(PhpDocInfoFactory::class), $container->make(StaticTypeMapper::class));
        });
        $lazyRectorConfig->afterResolving(ArrayTypeMapper::class, static function (ArrayTypeMapper $arrayTypeMapper, Container $container) : void {
            $arrayTypeMapper->autowire($container->make(PHPStanStaticTypeMapper::class));
        });
        $lazyRectorConfig->afterResolving(PlainValueParser::class, static function (PlainValueParser $plainValueParser, Container $container) : void {
            $plainValueParser->autowire($container->make(StaticDoctrineAnnotationParser::class), $container->make(ArrayParser::class));
        });
        $lazyRectorConfig->afterResolving(\Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper::class, static function (\Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper $unionTypeMapper, Container $container) : void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $unionTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $lazyRectorConfig->singleton(Parser::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createPHPStanParser();
        });
        $lazyRectorConfig->afterResolving(CurlyListNodeAnnotationToAttributeMapper::class, static function (CurlyListNodeAnnotationToAttributeMapper $curlyListNodeAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $curlyListNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $lazyRectorConfig->afterResolving(DoctrineAnnotationAnnotationToAttributeMapper::class, static function (DoctrineAnnotationAnnotationToAttributeMapper $doctrineAnnotationAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $doctrineAnnotationAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $lazyRectorConfig->when(PHPStanNodeScopeResolver::class)->needs('$nodeVisitors')->giveTagged(ScopeResolverNodeVisitorInterface::class);
        $this->registerTagged($lazyRectorConfig, self::SCOPE_RESOLVER_NODE_VISITOR_CLASSES, ScopeResolverNodeVisitorInterface::class);
        // phpstan factory
        $this->createPHPStanServices($lazyRectorConfig);
        // @todo add base node visitors
        $lazyRectorConfig->when(PhpDocNodeMapper::class)->needs('$phpDocNodeVisitors')->giveTagged(BasePhpDocNodeVisitorInterface::class);
        return $lazyRectorConfig;
    }
    /**
     * @param array<class-string> $classes
     * @param class-string $tagInterface
     */
    private function registerTagged(Container $container, array $classes, string $tagInterface) : void
    {
        foreach ($classes as $class) {
            Assert::isAOf($class, $tagInterface);
            $container->singleton($class);
            $container->tag($class, $tagInterface);
        }
    }
    private function createPHPStanServices(LazyRectorConfig $lazyRectorConfig) : void
    {
        $lazyRectorConfig->singleton(Parser::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createPHPStanParser();
        });
        $lazyRectorConfig->singleton(Lexer::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createEmulativeLexer();
        });
        foreach (self::PUBLIC_PHPSTAN_SERVICE_TYPES as $publicPhpstanServiceType) {
            $lazyRectorConfig->singleton($publicPhpstanServiceType, static function (Container $container) use($publicPhpstanServiceType) {
                $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
                return $phpstanServiceFactory->getByType($publicPhpstanServiceType);
            });
        }
    }
}
