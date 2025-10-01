<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\NodeFactory\SymfonyClosureFactory;
use Rector\Symfony\Configs\ConfigArrayHandler\NestedConfigCallsFactory;
use Rector\Symfony\Configs\ConfigArrayHandler\SecurityAccessDecisionManagerConfigArrayHandler;
use Rector\Symfony\Configs\Enum\DoctrineConfigKey;
use Rector\Symfony\Configs\Enum\SecurityConfigKey;
use Rector\Symfony\NodeAnalyzer\SymfonyClosureExtensionMatcher;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Rector\Symfony\Utils\StringUtils;
use Rector\Symfony\ValueObject\ExtensionKeyAndConfiguration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Closure\StringExtensionToConfigBuilderRector\StringExtensionToConfigBuilderRectorTest
 */
final class StringExtensionToConfigBuilderRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SymfonyPhpClosureDetector $symfonyPhpClosureDetector;
    /**
     * @readonly
     */
    private SymfonyClosureExtensionMatcher $symfonyClosureExtensionMatcher;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NestedConfigCallsFactory $nestedConfigCallsFactory;
    /**
     * @readonly
     */
    private SecurityAccessDecisionManagerConfigArrayHandler $securityAccessDecisionManagerConfigArrayHandler;
    /**
     * @readonly
     */
    private SymfonyClosureFactory $symfonyClosureFactory;
    /**
     * @var array<string, string>
     */
    private const EXTENSION_KEY_TO_CLASS_MAP = [
        'security' => 'Symfony\Config\SecurityConfig',
        'framework' => 'Symfony\Config\FrameworkConfig',
        'monolog' => 'Symfony\Config\MonologConfig',
        'twig' => 'Symfony\Config\TwigConfig',
        'doctrine' => 'Symfony\Config\DoctrineConfig',
        'doctrine_migrations' => 'Symfony\Config\DoctrineMigrationsConfig',
        'sentry' => 'Symfony\Config\SentryConfig',
        'web_profiler' => 'Symfony\Config\WebProfilerConfig',
        'debug' => 'Symfony\Config\DebugConfig',
        'maker' => 'Symfony\Config\MakerConfig',
        'nelmio_cors' => 'Symfony\Config\NelmioCorsConfig',
        'api_platform' => 'Symfony\Config\ApiPlatformConfig',
        // @see https://github.com/thephpleague/flysystem-bundle/blob/3.x/src/DependencyInjection/Configuration.php
        'flysystem' => 'Symfony\Config\FlysystemConfig',
    ];
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, SymfonyClosureExtensionMatcher $symfonyClosureExtensionMatcher, PropertyNaming $propertyNaming, ValueResolver $valueResolver, NestedConfigCallsFactory $nestedConfigCallsFactory, SecurityAccessDecisionManagerConfigArrayHandler $securityAccessDecisionManagerConfigArrayHandler, SymfonyClosureFactory $symfonyClosureFactory)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->symfonyClosureExtensionMatcher = $symfonyClosureExtensionMatcher;
        $this->propertyNaming = $propertyNaming;
        $this->valueResolver = $valueResolver;
        $this->nestedConfigCallsFactory = $nestedConfigCallsFactory;
        $this->securityAccessDecisionManagerConfigArrayHandler = $securityAccessDecisionManagerConfigArrayHandler;
        $this->symfonyClosureFactory = $symfonyClosureFactory;
        // make sure to avoid duplicates
        Assert::uniqueValues(self::EXTENSION_KEY_TO_CLASS_MAP);
        Assert::uniqueValues(array_keys(self::EXTENSION_KEY_TO_CLASS_MAP));
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert PHP fluent configs to Symfony 5.3 builder classes with method API', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
        ],
    ]);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $extensionKeyAndConfiguration = $this->symfonyClosureExtensionMatcher->match($node);
        if (!$extensionKeyAndConfiguration instanceof ExtensionKeyAndConfiguration) {
            return null;
        }
        $configClass = self::EXTENSION_KEY_TO_CLASS_MAP[$extensionKeyAndConfiguration->getKey()] ?? null;
        if ($configClass === null) {
            // better return null, to avoid failure on 3rd party extension that do not have a config class
            return null;
        }
        $configVariable = $this->createConfigVariable($configClass);
        $methodCallStmts = $this->createMethodCallStmts($extensionKeyAndConfiguration->getArray(), $configVariable);
        if ($methodCallStmts === null) {
            return null;
        }
        return $this->symfonyClosureFactory->create($configClass, $node, $methodCallStmts);
    }
    /**
     * @return array<Expression<MethodCall>>
     */
    private function createMethodCallStmts(Array_ $configurationArray, Variable $configVariable): ?array
    {
        $methodCallStmts = [];
        $configurationValues = $this->valueResolver->getValue($configurationArray);
        foreach ($configurationValues as $key => $value) {
            $splitMany = \false;
            $nested = \false;
            $nextKeyArgument = \false;
            if (in_array($key, [DoctrineConfigKey::DBAL, DoctrineConfigKey::ORM], \true)) {
                // doctrine
                $methodCallName = $key;
                $splitMany = \true;
                $nested = \true;
            } elseif ($key === 'handlers') {
                // monolog
                $methodCallName = 'handler';
                $splitMany = \true;
                $nextKeyArgument = \true;
            } elseif ($key === SecurityConfigKey::PROVIDERS) {
                // symfony security
                $methodCallName = SecurityConfigKey::PROVIDER;
                $splitMany = \true;
            } elseif ($key === SecurityConfigKey::FIREWALLS) {
                $methodCallName = SecurityConfigKey::FIREWALL;
                $splitMany = \true;
            } elseif ($key === SecurityConfigKey::ACCESS_CONTROL) {
                $splitMany = \true;
                $methodCallName = 'accessControl';
            } else {
                $methodCallName = StringUtils::underscoreToCamelCase($key);
            }
            // security
            if (in_array($key, [SecurityConfigKey::ACCESS_DECISION_MANAGER, SecurityConfigKey::ENTITY], \true)) {
                $mainMethodName = StringUtils::underscoreToCamelCase($key);
                $accessDecisionManagerMethodCalls = $this->securityAccessDecisionManagerConfigArrayHandler->handle($configurationArray, $configVariable, $mainMethodName);
                if ($accessDecisionManagerMethodCalls !== []) {
                    $methodCallStmts = array_merge($methodCallStmts, $accessDecisionManagerMethodCalls);
                    continue;
                }
            }
            if ($splitMany) {
                $currentConfigCaller = $nested ? new MethodCall($configVariable, $methodCallName) : $configVariable;
                if (!is_array($value)) {
                    return null;
                }
                foreach ($value as $itemName => $itemConfiguration) {
                    if ($nested && is_array($itemConfiguration)) {
                        $methodCallName = $itemName;
                    }
                    if (!is_array($itemConfiguration)) {
                        // simple call
                        $args = $this->nodeFactory->createArgs([$itemConfiguration]);
                        $itemName = StringUtils::underscoreToCamelCase($itemName);
                        // doctrine: implicit default connection now must be explicit
                        // this option requires call on connection(...)
                        if ($currentConfigCaller instanceof MethodCall && $this->isName($currentConfigCaller->name, 'dbal') && $itemName === 'dbnameSuffix') {
                            $currentConfigCaller = new MethodCall($currentConfigCaller, 'connection', $this->nodeFactory->createArgs(['default']));
                        }
                        $methodCall = new MethodCall($currentConfigCaller, $itemName, $args);
                        $methodCallStmts[] = new Expression($methodCall);
                        continue;
                    }
                    if ($currentConfigCaller instanceof MethodCall && $this->isName($currentConfigCaller->name, 'orm') && in_array($itemName, ['query_cache_driver', 'result_cache_driver'], \true)) {
                        // implicit entityManagerDefault(...)
                        $currentConfigCaller = new MethodCall($currentConfigCaller, 'entityManager', $this->nodeFactory->createArgs(['default']));
                    }
                    $nextMethodCallExpressions = $this->nestedConfigCallsFactory->create([$itemConfiguration], $currentConfigCaller, $methodCallName, $nextKeyArgument, $itemName);
                    $methodCallStmts = array_merge($methodCallStmts, $nextMethodCallExpressions);
                }
            } else {
                // skip empty values
                if ($value === null) {
                    continue;
                }
                $simpleMethodName = StringUtils::underscoreToCamelCase($key);
                if (is_array($value)) {
                    $simpleMethodCallStmts = $this->nestedConfigCallsFactory->create([$value], $configVariable, $simpleMethodName, \false);
                    $methodCallStmts = array_merge($methodCallStmts, $simpleMethodCallStmts);
                } else {
                    $args = $this->nodeFactory->createArgs([$value]);
                    $methodCall = new MethodCall($configVariable, $simpleMethodName, $args);
                    $methodCallStmts[] = new Expression($methodCall);
                }
            }
        }
        return $methodCallStmts;
    }
    private function createConfigVariable(string $configClass): Variable
    {
        $variableName = $this->propertyNaming->fqnToVariableName($configClass);
        return new Variable($variableName);
    }
}
