<?php

declare (strict_types=1);
namespace Rector\DependencyInjection;

use RectorPrefix202608\Doctrine\Inflector\Inflector;
use RectorPrefix202608\Doctrine\Inflector\Rules\English\InflectorFactory;
use PhpParser\Lexer;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Parser\Parser;
use PHPStan\Php\PhpVersionFactory;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\ParserConfig;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Application\ChangedNodeScopeRefresher;
use Rector\Application\FileProcessor;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ArrayTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\CallableTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\IntersectionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\TemplatePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\UnionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocParser\ArrayItemClassNameDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\ConstExprClassNameDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocTagGenericUsesDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Rector\Caching\Cache;
use Rector\Caching\CacheFactory;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\ChangesReporting\Output\GitHubOutputFormatter;
use Rector\ChangesReporting\Output\GitlabOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\AliasClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ClassLikeNameClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\FullyQualifiedNameClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\OriginalNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ReservedClassNameImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ShortClassImportSkipVoter;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\UsesClassNameImportSkipVoter;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Config\RectorConfig;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\Console\Command\ComposerBasedCommand;
use Rector\Console\Command\CustomRuleCommand;
use Rector\Console\Command\ListRulesCommand;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\Command\SetupCICommand;
use Rector\Console\Command\WorkerCommand;
use Rector\Console\ConsoleApplication;
use Rector\Console\Style\SymfonyStyleFactory;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
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
use Rector\NodeTypeResolver\NodeTypeResolver\ClassConstFetchTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\NameTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\NewTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ParamTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\PropertyTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\ScalarTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\StaticCallMethodCallTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver\TraitTypeResolver;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Php80\AttributeDecorator\DoctrineConverterAttributeDecorator;
use Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator;
use Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
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
use Rector\PhpParser\NodeVisitor\ArgNodeVisitor;
use Rector\PhpParser\NodeVisitor\ArgNotAcceptingClosureNodeVisitor;
use Rector\PhpParser\NodeVisitor\AssignedToNodeVisitor;
use Rector\PhpParser\NodeVisitor\ByRefReturnNodeVisitor;
use Rector\PhpParser\NodeVisitor\ByRefVariableNodeVisitor;
use Rector\PhpParser\NodeVisitor\CallLikeThisBoundClosureArgsNodeVisitor;
use Rector\PhpParser\NodeVisitor\ClassConstFetchNodeVisitor;
use Rector\PhpParser\NodeVisitor\ClosureWithVariadicParametersNodeVisitor;
use Rector\PhpParser\NodeVisitor\ContextNodeVisitor;
use Rector\PhpParser\NodeVisitor\GlobalVariableNodeVisitor;
use Rector\PhpParser\NodeVisitor\NameNodeVisitor;
use Rector\PhpParser\NodeVisitor\ParamDefaultNodeVisitor;
use Rector\PhpParser\NodeVisitor\PhpVersionConditionNodeVisitor;
use Rector\PhpParser\NodeVisitor\PropertyOrClassConstDefaultNodeVisitor;
use Rector\PhpParser\NodeVisitor\StaticVariableNodeVisitor;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\BooleanTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\CallableTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ClassStringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ClosureTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeForParameterMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConstantArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\FloatTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasMethodTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\HasPropertyTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\IntegerTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\IntersectionTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\IterableTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\MixedTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\NeverTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\NullTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ParentStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ResourceTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StrictMixedTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\StringTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\TypeWithClassNameTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\VoidTypeMapper;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Rector\AbstractRector;
use Rector\Skipper\Skipper\Skipper;
use Rector\Skipper\Skipper\UsedSkipCollector;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
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
use RectorPrefix202608\Symfony\Component\Console\Application;
use RectorPrefix202608\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202608\Webmozart\Assert\Assert;
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
     * @var array<class-string<DecoratingNodeVisitorInterface>>
     */
    private const DECORATING_NODE_VISITOR_CLASSES = [ArgNodeVisitor::class, ClosureWithVariadicParametersNodeVisitor::class, PhpVersionConditionNodeVisitor::class, AssignedToNodeVisitor::class, ByRefReturnNodeVisitor::class, ByRefVariableNodeVisitor::class, ContextNodeVisitor::class, GlobalVariableNodeVisitor::class, NameNodeVisitor::class, StaticVariableNodeVisitor::class, PropertyOrClassConstDefaultNodeVisitor::class, ParamDefaultNodeVisitor::class, ClassConstFetchNodeVisitor::class, CallLikeThisBoundClosureArgsNodeVisitor::class, ArgNotAcceptingClosureNodeVisitor::class];
    /**
     * @var array<class-string<PhpDocTypeMapperInterface>>
     */
    private const PHPDOC_TYPE_MAPPER_CLASSES = [IdentifierPhpDocTypeMapper::class, IntersectionPhpDocTypeMapper::class, NullablePhpDocTypeMapper::class, UnionPhpDocTypeMapper::class];
    /**
     * @var array<class-string<ClassNameImportSkipVoterInterface>>
     */
    private const CLASS_NAME_IMPORT_SKIPPER_CLASSES = [AliasClassNameImportSkipVoter::class, ClassLikeNameClassNameImportSkipVoter::class, FullyQualifiedNameClassNameImportSkipVoter::class, UsesClassNameImportSkipVoter::class, ReservedClassNameImportSkipVoter::class, ShortClassImportSkipVoter::class, OriginalNameImportSkipVoter::class];
    /**
     * @var array<class-string<TypeMapperInterface>>
     */
    private const TYPE_MAPPER_CLASSES = [AccessoryArrayTypeMapper::class, AccessoryStringTypeMapper::class, ConstantArrayTypeMapper::class, ArrayTypeMapper::class, BooleanTypeMapper::class, CallableTypeMapper::class, ClassStringTypeMapper::class, ClosureTypeMapper::class, ConditionalTypeForParameterMapper::class, ConditionalTypeMapper::class, FloatTypeMapper::class, HasMethodTypeMapper::class, HasPropertyTypeMapper::class, IntegerTypeMapper::class, IntersectionTypeMapper::class, IterableTypeMapper::class, MixedTypeMapper::class, NeverTypeMapper::class, NullTypeMapper::class, ObjectTypeMapper::class, ObjectWithoutClassTypeMapper::class, ParentStaticTypeMapper::class, ResourceTypeMapper::class, StaticTypeMapper::class, StrictMixedTypeMapper::class, StringTypeMapper::class, TypeWithClassNameTypeMapper::class, UnionTypeMapper::class, VoidTypeMapper::class];
    /**
     * @var array<class-string<PhpDocNodeDecoratorInterface>>
     */
    private const PHP_DOC_NODE_DECORATOR_CLASSES = [ConstExprClassNameDecorator::class, DoctrineAnnotationDecorator::class, ArrayItemClassNameDecorator::class, PhpDocTagGenericUsesDecorator::class];
    /**
     * @var array<class-string>
     */
    private const PUBLIC_PHPSTAN_SERVICE_TYPES = [ScopeFactory::class, TypeNodeResolver::class, NodeScopeResolver::class, ReflectionProvider::class, PhpVersionFactory::class];
    /**
     * @var array<class-string<OutputFormatterInterface>>
     */
    private const OUTPUT_FORMATTER_CLASSES = [ConsoleOutputFormatter::class, JsonOutputFormatter::class, GitlabOutputFormatter::class, GitHubOutputFormatter::class];
    /**
     * @var array<class-string<NodeTypeResolverInterface>>
     */
    private const NODE_TYPE_RESOLVER_CLASSES = [CastTypeResolver::class, StaticCallMethodCallTypeResolver::class, ClassAndInterfaceTypeResolver::class, IdentifierTypeResolver::class, NameTypeResolver::class, NewTypeResolver::class, ParamTypeResolver::class, PropertyFetchTypeResolver::class, ClassConstFetchTypeResolver::class, PropertyTypeResolver::class, ScalarTypeResolver::class, TraitTypeResolver::class];
    /**
     * @var array<class-string<PhpParserNodeMapperInterface>>
     */
    private const PHP_PARSER_NODE_MAPPER_CLASSES = [FullyQualifiedNodeMapper::class, IdentifierNodeMapper::class, IntersectionTypeNodeMapper::class, NameNodeMapper::class, NullableTypeNodeMapper::class, StringNodeMapper::class, UnionTypeNodeMapper::class, ExprNodeMapper::class];
    /**
     * @var array<class-string<ConverterAttributeDecoratorInterface>>
     */
    private const CONVERTER_ATTRIBUTE_DECORATOR_CLASSES = [SensioParamConverterAttributeDecorator::class, DoctrineConverterAttributeDecorator::class];
    /**
     * @api used as next rectorConfig factory
     */
    public function create(): RectorConfig
    {
        $rectorConfig = new RectorConfig();
        $rectorConfig->import(__DIR__ . '/../../config/config.php');
        $this->registerConsole($rectorConfig);
        $this->registerFileProcessing($rectorConfig);
        $this->registerCachingAndResettables($rectorConfig);
        $this->registerTypeMappers($rectorConfig);
        $this->registerNodeNameResolvers($rectorConfig);
        $this->registerRectorAutowiring($rectorConfig);
        $this->registerTaggedServices($rectorConfig);
        $this->registerAnnotationToAttributeSetters($rectorConfig);
        $this->registerNodeVisitorsAndPhpDoc($rectorConfig);
        return $rectorConfig;
    }
    private function registerConsole(RectorConfig $rectorConfig): void
    {
        $rectorConfig->singleton(Application::class, static function (RectorConfig $rectorConfig): Application {
            $consoleApplication = $rectorConfig->make(ConsoleApplication::class);
            $commandNamesToHide = ['list', 'completion', 'help', 'worker'];
            foreach ($commandNamesToHide as $commandNameToHide) {
                $commandToHide = $consoleApplication->get($commandNameToHide);
                $commandToHide->setHidden();
            }
            return $consoleApplication;
        });
        $rectorConfig->singleton(Inflector::class, static function (): Inflector {
            $inflectorFactory = new InflectorFactory();
            return $inflectorFactory->build();
        });
        $rectorConfig->singleton(ConfigurationRuleFilter::class);
        $rectorConfig->singleton(ProcessCommand::class);
        $rectorConfig->singleton(WorkerCommand::class);
        $rectorConfig->singleton(SetupCICommand::class);
        $rectorConfig->singleton(ListRulesCommand::class);
        $rectorConfig->singleton(CustomRuleCommand::class);
        $rectorConfig->singleton(ComposerBasedCommand::class);
    }
    private function registerFileProcessing(RectorConfig $rectorConfig): void
    {
        $rectorConfig->singleton(FileProcessor::class);
        $rectorConfig->singleton(PostFileProcessor::class);
        // shared state: collects used skips across the skipper, the path skipper and the file processor
        $rectorConfig->singleton(UsedSkipCollector::class);
        $rectorConfig->singleton(DynamicSourceLocatorProvider::class, static function (RectorConfig $rectorConfig): DynamicSourceLocatorProvider {
            $phpStanServicesFactory = $rectorConfig->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createDynamicSourceLocatorProvider();
        });
    }
    private function registerCachingAndResettables(RectorConfig $rectorConfig): void
    {
        // resettable: registering the class makes it discoverable via findByContract(ResettableInterface)
        $rectorConfig->singleton(RenamedClassesDataCollector::class);
        // caching
        $rectorConfig->singleton(Cache::class, static function (RectorConfig $rectorConfig): Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $rectorConfig->make(CacheFactory::class);
            return $cacheFactory->create();
        });
    }
    private function registerTypeMappers(RectorConfig $rectorConfig): void
    {
        // tagged services
        $rectorConfig->afterResolving(ArrayTypeMapper::class, static function (ArrayTypeMapper $arrayTypeMapper) use ($rectorConfig): void {
            $arrayTypeMapper->autowire($rectorConfig->make(PHPStanStaticTypeMapper::class));
        });
        $rectorConfig->afterResolving(ConditionalTypeForParameterMapper::class, static function (ConditionalTypeForParameterMapper $conditionalTypeForParameterMapper) use ($rectorConfig): void {
            $phpStanStaticTypeMapper = $rectorConfig->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeForParameterMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(ConditionalTypeMapper::class, static function (ConditionalTypeMapper $conditionalTypeMapper) use ($rectorConfig): void {
            $phpStanStaticTypeMapper = $rectorConfig->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(UnionTypeMapper::class, static function (UnionTypeMapper $unionTypeMapper) use ($rectorConfig): void {
            $phpStanStaticTypeMapper = $rectorConfig->make(PHPStanStaticTypeMapper::class);
            $unionTypeMapper->autowire($phpStanStaticTypeMapper);
        });
    }
    private function registerNodeNameResolvers(RectorConfig $rectorConfig): void
    {
        // node name resolvers
        $this->registerTagged($rectorConfig, self::CONVERTER_ATTRIBUTE_DECORATOR_CLASSES, ConverterAttributeDecoratorInterface::class);
    }
    private function registerRectorAutowiring(RectorConfig $rectorConfig): void
    {
        $rectorConfig->afterResolving(AbstractRector::class, static function (AbstractRector $rector) use ($rectorConfig): void {
            $rector->autowire($rectorConfig->get(NodeNameResolver::class), $rectorConfig->get(NodeTypeResolver::class), $rectorConfig->get(SimpleCallableNodeTraverser::class), $rectorConfig->get(NodeFactory::class), $rectorConfig->get(Skipper::class), $rectorConfig->get(NodeComparator::class), $rectorConfig->get(CurrentFileProvider::class), $rectorConfig->get(CreatedByRuleDecorator::class), $rectorConfig->get(ChangedNodeScopeRefresher::class), $rectorConfig->get(CommentsMerger::class));
        });
        $this->registerTagged($rectorConfig, self::PHP_PARSER_NODE_MAPPER_CLASSES, PhpParserNodeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHP_DOC_NODE_DECORATOR_CLASSES, PhpDocNodeDecoratorInterface::class);
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
    }
    private function registerTaggedServices(RectorConfig $rectorConfig): void
    {
        // PHP 8.0 attributes
        $this->registerTagged($rectorConfig, self::ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES, AnnotationToAttributeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::TYPE_MAPPER_CLASSES, TypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHPDOC_TYPE_MAPPER_CLASSES, PhpDocTypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_NAME_RESOLVER_CLASSES, NodeNameResolverInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_TYPE_RESOLVER_CLASSES, NodeTypeResolverInterface::class);
        $this->registerTagged($rectorConfig, self::OUTPUT_FORMATTER_CLASSES, OutputFormatterInterface::class);
        $this->registerTagged($rectorConfig, self::CLASS_NAME_IMPORT_SKIPPER_CLASSES, ClassNameImportSkipVoterInterface::class);
        $rectorConfig->singleton(SymfonyStyle::class, static function (RectorConfig $rectorConfig): SymfonyStyle {
            $symfonyStyleFactory = $rectorConfig->make(SymfonyStyleFactory::class);
            return $symfonyStyleFactory->create();
        });
    }
    private function registerAnnotationToAttributeSetters(RectorConfig $rectorConfig): void
    {
        // required-like setter
        $rectorConfig->afterResolving(ArrayAnnotationToAttributeMapper::class, static function (ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper) use ($rectorConfig): void {
            $annotationToAttributeMapper = $rectorConfig->make(AnnotationToAttributeMapper::class);
            $arrayAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(ArrayItemNodeAnnotationToAttributeMapper::class, static function (ArrayItemNodeAnnotationToAttributeMapper $arrayItemNodeAnnotationToAttributeMapper) use ($rectorConfig): void {
            $annotationToAttributeMapper = $rectorConfig->make(AnnotationToAttributeMapper::class);
            $arrayItemNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(PlainValueParser::class, static function (PlainValueParser $plainValueParser) use ($rectorConfig): void {
            $plainValueParser->autowire($rectorConfig->make(StaticDoctrineAnnotationParser::class), $rectorConfig->make(ArrayParser::class));
        });
        $rectorConfig->afterResolving(CurlyListNodeAnnotationToAttributeMapper::class, static function (CurlyListNodeAnnotationToAttributeMapper $curlyListNodeAnnotationToAttributeMapper) use ($rectorConfig): void {
            $annotationToAttributeMapper = $rectorConfig->make(AnnotationToAttributeMapper::class);
            $curlyListNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(DoctrineAnnotationAnnotationToAttributeMapper::class, static function (DoctrineAnnotationAnnotationToAttributeMapper $doctrineAnnotationAnnotationToAttributeMapper) use ($rectorConfig): void {
            $annotationToAttributeMapper = $rectorConfig->make(AnnotationToAttributeMapper::class);
            $doctrineAnnotationAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
    }
    private function registerNodeVisitorsAndPhpDoc(RectorConfig $rectorConfig): void
    {
        $this->registerTagged($rectorConfig, self::DECORATING_NODE_VISITOR_CLASSES, DecoratingNodeVisitorInterface::class);
        $this->createPHPStanServices($rectorConfig);
        // phpdoc-parser
        $rectorConfig->singleton(ParserConfig::class, static fn(RectorConfig $rectorConfig): ParserConfig => new ParserConfig(['lines' => \true, 'indexes' => \true, 'comments' => \true]));
    }
    /**
     * @param array<class-string> $classes
     * @param class-string $tagInterface
     */
    private function registerTagged(RectorConfig $rectorConfig, array $classes, string $tagInterface): void
    {
        foreach ($classes as $class) {
            Assert::isAOf($class, $tagInterface);
            $rectorConfig->singleton($class);
        }
    }
    private function createPHPStanServices(RectorConfig $rectorConfig): void
    {
        $rectorConfig->singleton(Parser::class, static function (RectorConfig $rectorConfig) {
            $phpStanServicesFactory = $rectorConfig->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createPHPStanParser();
        });
        $rectorConfig->singleton(Lexer::class, static function (RectorConfig $rectorConfig) {
            $phpStanServicesFactory = $rectorConfig->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createEmulativeLexer();
        });
        foreach (self::PUBLIC_PHPSTAN_SERVICE_TYPES as $publicPhpstanServiceType) {
            $rectorConfig->singleton($publicPhpstanServiceType, static function (RectorConfig $rectorConfig) use ($publicPhpstanServiceType) {
                $phpStanServicesFactory = $rectorConfig->make(PHPStanServicesFactory::class);
                return $phpStanServicesFactory->getByType($publicPhpstanServiceType);
            });
        }
    }
}
