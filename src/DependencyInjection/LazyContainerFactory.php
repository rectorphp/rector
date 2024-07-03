<?php

declare (strict_types=1);
namespace Rector\DependencyInjection;

use RectorPrefix202407\Doctrine\Inflector\Inflector;
use RectorPrefix202407\Doctrine\Inflector\Rules\English\InflectorFactory;
use RectorPrefix202407\Illuminate\Container\Container;
use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\Reflection\BetterReflection\SourceLocator\CachingVisitor;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Application\ChangedNodeScopeRefresher;
use Rector\Application\FileProcessor;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ArrayTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\CallableTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\IntersectionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\TemplatePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\UnionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocParser\ArrayItemClassNameDecorator;
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
use Rector\Config\RectorConfig;
use Rector\Configuration\ConfigInitializer;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\Console\Command\CustomRuleCommand;
use Rector\Console\Command\DetectNodeCommand;
use Rector\Console\Command\ListRulesCommand;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\Command\SetupCICommand;
use Rector\Console\Command\WorkerCommand;
use Rector\Console\ConsoleApplication;
use Rector\Console\Output\OutputFormatterCollector;
use Rector\Console\Style\RectorStyle;
use Rector\Console\Style\SymfonyStyleFactory;
use Rector\Contract\DependencyInjection\ResetableInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeDecorator\CreatedByRuleDecorator;
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
use Rector\Php80\AttributeDecorator\DoctrineConverterAttributeDecorator;
use Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator;
use Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
use Rector\Php80\NodeManipulator\AttributeGroupNamedArgumentManipulator;
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
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
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
use Rector\PHPStanStaticTypeMapper\TypeMapper\IntersectionTypeMapper;
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
use Rector\PHPStanStaticTypeMapper\TypeMapper\StaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StrictMixedTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ThisTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\TypeWithClassNameTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\VoidTypeMapper;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Rector\AbstractRector;
use Rector\Skipper\Contract\SkipVoterInterface;
use Rector\Skipper\Skipper\Skipper;
use Rector\Skipper\SkipVoter\ClassSkipVoter;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\IntersectionPhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\NullablePhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpDocParser\UnionPhpDocTypeMapper;
use Rector\StaticTypeMapper\PhpParser\ExprNodeMapper;
use Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper;
use Rector\StaticTypeMapper\PhpParser\IntersectionTypeNodeMapper;
use Rector\StaticTypeMapper\PhpParser\NameNodeMapper;
use Rector\StaticTypeMapper\PhpParser\NullableTypeNodeMapper;
use Rector\StaticTypeMapper\PhpParser\StringNodeMapper;
use Rector\StaticTypeMapper\PhpParser\UnionTypeNodeMapper;
use RectorPrefix202407\Symfony\Component\Console\Application;
use RectorPrefix202407\Symfony\Component\Console\Command\Command;
use RectorPrefix202407\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202407\Webmozart\Assert\Assert;
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
    private const PHPDOC_TYPE_MAPPER_CLASSES = [IdentifierPhpDocTypeMapper::class, IntersectionPhpDocTypeMapper::class, NullablePhpDocTypeMapper::class, UnionPhpDocTypeMapper::class];
    /**
     * @var array<class-string<ClassNameImportSkipVoterInterface>>
     */
    private const CLASS_NAME_IMPORT_SKIPPER_CLASSES = [AliasClassNameImportSkipVoter::class, ClassLikeNameClassNameImportSkipVoter::class, FullyQualifiedNameClassNameImportSkipVoter::class, UsesClassNameImportSkipVoter::class];
    /**
     * @var array<class-string<TypeMapperInterface>>
     */
    private const TYPE_MAPPER_CLASSES = [AccessoryLiteralStringTypeMapper::class, AccessoryNonEmptyStringTypeMapper::class, AccessoryNonFalsyStringTypeMapper::class, AccessoryNumericStringTypeMapper::class, ArrayTypeMapper::class, BooleanTypeMapper::class, CallableTypeMapper::class, ClassStringTypeMapper::class, ClosureTypeMapper::class, ConditionalTypeForParameterMapper::class, ConditionalTypeMapper::class, FloatTypeMapper::class, GenericClassStringTypeMapper::class, HasMethodTypeMapper::class, HasOffsetTypeMapper::class, HasOffsetValueTypeTypeMapper::class, HasPropertyTypeMapper::class, IntegerTypeMapper::class, IntersectionTypeMapper::class, IterableTypeMapper::class, MixedTypeMapper::class, NeverTypeMapper::class, NonEmptyArrayTypeMapper::class, NullTypeMapper::class, ObjectTypeMapper::class, ObjectWithoutClassTypeMapper::class, OversizedArrayTypeMapper::class, ParentStaticTypeMapper::class, ResourceTypeMapper::class, SelfObjectTypeMapper::class, StaticTypeMapper::class, StrictMixedTypeMapper::class, StringTypeMapper::class, ThisTypeMapper::class, TypeWithClassNameTypeMapper::class, UnionTypeMapper::class, VoidTypeMapper::class];
    /**
     * @var array<class-string<PhpDocNodeDecoratorInterface>>
     */
    private const PHP_DOC_NODE_DECORATOR_CLASSES = [ConstExprClassNameDecorator::class, DoctrineAnnotationDecorator::class, ArrayItemClassNameDecorator::class];
    /**
     * @var array<class-string>
     */
    private const PUBLIC_PHPSTAN_SERVICE_TYPES = [ScopeFactory::class, TypeNodeResolver::class, FileHelper::class, NodeScopeResolver::class, ReflectionProvider::class, CachingVisitor::class];
    /**
     * @var array<class-string<OutputFormatterInterface>>
     */
    private const OUTPUT_FORMATTER_CLASSES = [ConsoleOutputFormatter::class, JsonOutputFormatter::class];
    /**
     * @var array<class-string<NodeTypeResolverInterface>>
     */
    private const NODE_TYPE_RESOLVER_CLASSES = [CastTypeResolver::class, StaticCallMethodCallTypeResolver::class, ClassAndInterfaceTypeResolver::class, ClassMethodOrClassConstTypeResolver::class, IdentifierTypeResolver::class, NameTypeResolver::class, NewTypeResolver::class, ParamTypeResolver::class, PropertyFetchTypeResolver::class, PropertyTypeResolver::class, ScalarTypeResolver::class, TraitTypeResolver::class];
    /**
     * @var array<class-string<PhpParserNodeMapperInterface>>
     */
    private const PHP_PARSER_NODE_MAPPER_CLASSES = [FullyQualifiedNodeMapper::class, IdentifierNodeMapper::class, IntersectionTypeNodeMapper::class, NameNodeMapper::class, NullableTypeNodeMapper::class, StringNodeMapper::class, UnionTypeNodeMapper::class, ExprNodeMapper::class];
    /**
     * @var array<class-string<SkipVoterInterface>>
     */
    private const SKIP_VOTER_CLASSES = [ClassSkipVoter::class];
    /**
     * @var array<class-string<ConverterAttributeDecoratorInterface>>
     */
    private const CONVERTER_ATTRIBUTE_DECORATOR_CLASSES = [SensioParamConverterAttributeDecorator::class, DoctrineConverterAttributeDecorator::class];
    /**
     * @api used as next rectorConfig factory
     */
    public function create() : RectorConfig
    {
        $rectorConfig = new RectorConfig();
        $rectorConfig->import(__DIR__ . '/../../config/config.php');
        $rectorConfig->singleton(Application::class, static function (Container $container) : Application {
            $application = $container->make(ConsoleApplication::class);
            $commandNamesToHide = ['list', 'completion', 'help', 'worker'];
            foreach ($commandNamesToHide as $commandNameToHide) {
                $commandToHide = $application->get($commandNameToHide);
                $commandToHide->setHidden();
            }
            return $application;
        });
        $rectorConfig->when(ConsoleApplication::class)->needs('$commands')->giveTagged(Command::class);
        $rectorConfig->singleton(Inflector::class, static function () : Inflector {
            $inflectorFactory = new InflectorFactory();
            return $inflectorFactory->build();
        });
        $rectorConfig->singleton(ProcessCommand::class);
        $rectorConfig->singleton(WorkerCommand::class);
        $rectorConfig->singleton(SetupCICommand::class);
        $rectorConfig->singleton(ListRulesCommand::class);
        $rectorConfig->singleton(CustomRuleCommand::class);
        $rectorConfig->singleton(DetectNodeCommand::class);
        $rectorConfig->when(ListRulesCommand::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->singleton(FileProcessor::class);
        $rectorConfig->singleton(PostFileProcessor::class);
        // phpdoc-parser
        $rectorConfig->when(TypeParser::class)->needs('$usedAttributes')->give(['lines' => \true, 'indexes' => \true]);
        $rectorConfig->when(ConstExprParser::class)->needs('$usedAttributes')->give(['lines' => \true, 'indexes' => \true]);
        $rectorConfig->alias(TypeParser::class, BetterTypeParser::class);
        $rectorConfig->when(RectorNodeTraverser::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->when(ConfigInitializer::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->when(ClassNameImportSkipper::class)->needs('$classNameImportSkipVoters')->giveTagged(ClassNameImportSkipVoterInterface::class);
        $rectorConfig->singleton(DynamicSourceLocatorProvider::class, static function (Container $container) : DynamicSourceLocatorProvider {
            $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createDynamicSourceLocatorProvider();
        });
        // resetables
        $rectorConfig->tag(DynamicSourceLocatorProvider::class, ResetableInterface::class);
        $rectorConfig->tag(RenamedClassesDataCollector::class, ResetableInterface::class);
        // caching
        $rectorConfig->singleton(Cache::class, static function (Container $container) : Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $container->make(CacheFactory::class);
            return $cacheFactory->create();
        });
        // tagged services
        $rectorConfig->when(BetterPhpDocParser::class)->needs('$phpDocNodeDecorators')->giveTagged(PhpDocNodeDecoratorInterface::class);
        $rectorConfig->afterResolving(ArrayTypeMapper::class, static function (ArrayTypeMapper $arrayTypeMapper, Container $container) : void {
            $arrayTypeMapper->autowire($container->make(PHPStanStaticTypeMapper::class));
        });
        $rectorConfig->afterResolving(ConditionalTypeForParameterMapper::class, static function (ConditionalTypeForParameterMapper $conditionalTypeForParameterMapper, Container $container) : void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeForParameterMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(ConditionalTypeMapper::class, static function (ConditionalTypeMapper $conditionalTypeMapper, Container $container) : void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(UnionTypeMapper::class, static function (UnionTypeMapper $unionTypeMapper, Container $container) : void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $unionTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->when(PHPStanStaticTypeMapper::class)->needs('$typeMappers')->giveTagged(TypeMapperInterface::class);
        $rectorConfig->when(PhpDocTypeMapper::class)->needs('$phpDocTypeMappers')->giveTagged(PhpDocTypeMapperInterface::class);
        $rectorConfig->when(PhpParserNodeMapper::class)->needs('$phpParserNodeMappers')->giveTagged(PhpParserNodeMapperInterface::class);
        $rectorConfig->when(NodeTypeResolver::class)->needs('$nodeTypeResolvers')->giveTagged(NodeTypeResolverInterface::class);
        // node name resolvers
        $rectorConfig->when(NodeNameResolver::class)->needs('$nodeNameResolvers')->giveTagged(NodeNameResolverInterface::class);
        $rectorConfig->when(Skipper::class)->needs('$skipVoters')->giveTagged(SkipVoterInterface::class);
        $this->registerTagged($rectorConfig, self::SKIP_VOTER_CLASSES, SkipVoterInterface::class);
        $rectorConfig->when(AttributeGroupNamedArgumentManipulator::class)->needs('$converterAttributeDecorators')->giveTagged(ConverterAttributeDecoratorInterface::class);
        $this->registerTagged($rectorConfig, self::CONVERTER_ATTRIBUTE_DECORATOR_CLASSES, ConverterAttributeDecoratorInterface::class);
        $rectorConfig->afterResolving(AbstractRector::class, static function (AbstractRector $rector, Container $container) : void {
            $rector->autowire($container->get(NodeNameResolver::class), $container->get(NodeTypeResolver::class), $container->get(SimpleCallableNodeTraverser::class), $container->get(NodeFactory::class), $container->get(Skipper::class), $container->get(NodeComparator::class), $container->get(CurrentFileProvider::class), $container->get(CreatedByRuleDecorator::class), $container->get(ChangedNodeScopeRefresher::class));
        });
        $this->registerTagged($rectorConfig, self::PHP_PARSER_NODE_MAPPER_CLASSES, PhpParserNodeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHP_DOC_NODE_DECORATOR_CLASSES, PhpDocNodeDecoratorInterface::class);
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
        // PHP 8.0 attributes
        $this->registerTagged($rectorConfig, self::ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES, AnnotationToAttributeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::TYPE_MAPPER_CLASSES, TypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHPDOC_TYPE_MAPPER_CLASSES, PhpDocTypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_NAME_RESOLVER_CLASSES, NodeNameResolverInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_TYPE_RESOLVER_CLASSES, NodeTypeResolverInterface::class);
        $this->registerTagged($rectorConfig, self::OUTPUT_FORMATTER_CLASSES, OutputFormatterInterface::class);
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
        $this->registerTagged($rectorConfig, self::CLASS_NAME_IMPORT_SKIPPER_CLASSES, ClassNameImportSkipVoterInterface::class);
        $rectorConfig->alias(SymfonyStyle::class, RectorStyle::class);
        $rectorConfig->singleton(SymfonyStyle::class, static function (Container $container) : SymfonyStyle {
            $symfonyStyleFactory = $container->make(SymfonyStyleFactory::class);
            return $symfonyStyleFactory->create();
        });
        $rectorConfig->when(AnnotationToAttributeMapper::class)->needs('$annotationToAttributeMappers')->giveTagged(AnnotationToAttributeMapperInterface::class);
        $rectorConfig->when(OutputFormatterCollector::class)->needs('$outputFormatters')->giveTagged(OutputFormatterInterface::class);
        // required-like setter
        $rectorConfig->afterResolving(ArrayAnnotationToAttributeMapper::class, static function (ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributesMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayAnnotationToAttributeMapper->autowire($annotationToAttributesMapper);
        });
        $rectorConfig->afterResolving(ArrayItemNodeAnnotationToAttributeMapper::class, static function (ArrayItemNodeAnnotationToAttributeMapper $arrayItemNodeAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayItemNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(PlainValueParser::class, static function (PlainValueParser $plainValueParser, Container $container) : void {
            $plainValueParser->autowire($container->make(StaticDoctrineAnnotationParser::class), $container->make(ArrayParser::class));
        });
        $rectorConfig->afterResolving(CurlyListNodeAnnotationToAttributeMapper::class, static function (CurlyListNodeAnnotationToAttributeMapper $curlyListNodeAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $curlyListNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(DoctrineAnnotationAnnotationToAttributeMapper::class, static function (DoctrineAnnotationAnnotationToAttributeMapper $doctrineAnnotationAnnotationToAttributeMapper, Container $container) : void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $doctrineAnnotationAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->when(PHPStanNodeScopeResolver::class)->needs('$nodeVisitors')->giveTagged(ScopeResolverNodeVisitorInterface::class);
        $this->registerTagged($rectorConfig, self::SCOPE_RESOLVER_NODE_VISITOR_CLASSES, ScopeResolverNodeVisitorInterface::class);
        $this->createPHPStanServices($rectorConfig);
        $rectorConfig->when(PhpDocNodeMapper::class)->needs('$phpDocNodeVisitors')->giveTagged(BasePhpDocNodeVisitorInterface::class);
        return $rectorConfig;
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
    private function createPHPStanServices(RectorConfig $rectorConfig) : void
    {
        $rectorConfig->singleton(Parser::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createPHPStanParser();
        });
        $rectorConfig->singleton(Lexer::class, static function (Container $container) {
            $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
            return $phpstanServiceFactory->createEmulativeLexer();
        });
        foreach (self::PUBLIC_PHPSTAN_SERVICE_TYPES as $publicPhpstanServiceType) {
            $rectorConfig->singleton($publicPhpstanServiceType, static function (Container $container) use($publicPhpstanServiceType) {
                $phpstanServiceFactory = $container->make(PHPStanServicesFactory::class);
                return $phpstanServiceFactory->getByType($publicPhpstanServiceType);
            });
        }
    }
}
