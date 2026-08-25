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
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ArrayTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\CallableTypePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\IntersectionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\TemplatePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\UnionTypeNodePhpDocNodeVisitor;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Rector\Caching\Cache;
use Rector\Caching\CacheFactory;
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
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayItemNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\CurlyListNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\DoctrineAnnotationAnnotationToAttributeMapper;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\NodeVisitor\AssignedToNodeVisitor;
use Rector\PhpParser\NodeVisitor\ByRefNodeVisitor;
use Rector\PhpParser\NodeVisitor\CallLikeReflectionNodeVisitor;
use Rector\PhpParser\NodeVisitor\ContextNodeVisitor;
use Rector\PhpParser\NodeVisitor\DefaultValueNodeVisitor;
use Rector\PhpParser\NodeVisitor\LocalVariableScopeNodeVisitor;
use Rector\PhpParser\NodeVisitor\NameAndArgNodeVisitor;
use Rector\PhpParser\NodeVisitor\PhpVersionConditionNodeVisitor;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeForParameterMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Rector\AbstractRector;
use Rector\Skipper\Skipper\Skipper;
use Rector\Skipper\Skipper\UsedSkipCollector;
use RectorPrefix202608\Symfony\Component\Console\Application;
use RectorPrefix202608\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202608\Webmozart\Assert\Assert;
final class LazyContainerFactory
{
    /**
     * @var array<class-string<BasePhpDocNodeVisitorInterface>>
     */
    private const BASE_PHP_DOC_NODE_VISITORS = [ArrayTypePhpDocNodeVisitor::class, CallableTypePhpDocNodeVisitor::class, IntersectionTypeNodePhpDocNodeVisitor::class, TemplatePhpDocNodeVisitor::class, UnionTypeNodePhpDocNodeVisitor::class];
    /**
     * @var array<class-string<DecoratingNodeVisitorInterface>>
     */
    private const DECORATING_NODE_VISITOR_CLASSES = [CallLikeReflectionNodeVisitor::class, PhpVersionConditionNodeVisitor::class, AssignedToNodeVisitor::class, ByRefNodeVisitor::class, ContextNodeVisitor::class, LocalVariableScopeNodeVisitor::class, NameAndArgNodeVisitor::class, DefaultValueNodeVisitor::class];
    /**
     * @var array<class-string>
     */
    private const PUBLIC_PHPSTAN_SERVICE_TYPES = [ScopeFactory::class, TypeNodeResolver::class, NodeScopeResolver::class, ReflectionProvider::class, PhpVersionFactory::class];
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
        $rectorConfig->autodiscover(__DIR__ . '/../../rules/Php80/AttributeDecorator');
    }
    private function registerRectorAutowiring(RectorConfig $rectorConfig): void
    {
        $rectorConfig->afterResolving(AbstractRector::class, static function (AbstractRector $rector) use ($rectorConfig): void {
            $rector->autowire($rectorConfig->get(NodeNameResolver::class), $rectorConfig->get(NodeTypeResolver::class), $rectorConfig->get(SimpleCallableNodeTraverser::class), $rectorConfig->get(NodeFactory::class), $rectorConfig->get(Skipper::class), $rectorConfig->get(NodeComparator::class), $rectorConfig->get(CurrentFileProvider::class), $rectorConfig->get(CreatedByRuleDecorator::class), $rectorConfig->get(ChangedNodeScopeRefresher::class), $rectorConfig->get(CommentsMerger::class));
        });
        $rectorConfig->autodiscover(__DIR__ . '/../StaticTypeMapper/PhpParser');
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
    }
    private function registerTaggedServices(RectorConfig $rectorConfig): void
    {
        // PHP 8.0 attributes
        $rectorConfig->autodiscover(__DIR__ . '/../PhpAttribute/AnnotationToAttributeMapper');
        $rectorConfig->autodiscover(__DIR__ . '/../PHPStanStaticTypeMapper/TypeMapper');
        $rectorConfig->autodiscover(__DIR__ . '/../StaticTypeMapper/PhpDocParser');
        $rectorConfig->autodiscover(__DIR__ . '/../NodeNameResolver/NodeNameResolver');
        $rectorConfig->autodiscover(__DIR__ . '/../NodeTypeResolver/NodeTypeResolver');
        $rectorConfig->autodiscover(__DIR__ . '/../ChangesReporting/Output');
        $rectorConfig->autodiscover(__DIR__ . '/../../rules/CodingStyle/ClassNameImport/ClassNameImportSkipVoter');
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
