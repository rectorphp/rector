<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Symfony\NodeAnalyzer\SymfonyClosureExtensionMatcher;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Rector\Symfony\ValueObject\ExtensionKeyAndConfiguration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Closure\StringExtensionToConfigBuilderRector\StringExtensionToConfigBuilderRectorTest
 */
final class StringExtensionToConfigBuilderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyClosureExtensionMatcher
     */
    private $symfonyClosureExtensionMatcher;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var array<string, string>
     */
    private const EXTENSION_KEY_TO_CLASS_MAP = ['security' => 'Symfony\\Config\\SecurityConfig', 'framework' => 'Symfony\\Config\\FrameworkConfig'];
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, SymfonyClosureExtensionMatcher $symfonyClosureExtensionMatcher, PropertyNaming $propertyNaming)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->symfonyClosureExtensionMatcher = $symfonyClosureExtensionMatcher;
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add config builder classes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'providers' => [
            'webservice' => [
                'id' => LoginServiceUserProvider::class,
            ],
        ],
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
    $securityConfig->provider('webservice', [
        'id' => LoginServiceUserProvider::class,
    ]);

    $securityConfig->firewall('dev', [
        'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
        'security' => false,
    ]);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
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
            throw new NotImplementedYetException($extensionKeyAndConfiguration->getKey());
        }
        return $this->createConfigClosureStmts($configClass, $node, $extensionKeyAndConfiguration);
    }
    private function createConfigClosureStmts(string $configClass, Closure $closure, ExtensionKeyAndConfiguration $extensionKeyAndConfiguration) : Closure
    {
        $closure->params[0] = $this->createConfigParam($configClass);
        $configuration = $extensionKeyAndConfiguration->getArray();
        $configVariable = $this->createConfigVariable($configClass);
        $closure->stmts = $this->createMethodCallStmts($configuration, $configVariable);
        return $closure;
    }
    /**
     * @return array<Expression<MethodCall>>
     */
    private function createMethodCallStmts(Array_ $configurationArray, Variable $configVariable) : array
    {
        $methodCallStmts = [];
        $configurationValues = $this->valueResolver->getValue($configurationArray);
        foreach ($configurationValues as $key => $value) {
            $splitMany = \false;
            if ($key === 'providers') {
                $methodCallName = 'provider';
                $splitMany = \true;
            } elseif ($key === 'firewalls') {
                $methodCallName = 'firewall';
                $splitMany = \true;
            } else {
                $methodCallName = $this->createCamelCaseFromUnderscored($key);
            }
            if ($splitMany) {
                foreach ($value as $itemName => $itemConfiguration) {
                    $fluentMethodCall = $this->createNextMethodCall([$itemName, $itemConfiguration], $configVariable, $methodCallName);
                    $methodCallStmts[] = new Expression($fluentMethodCall);
                }
            } else {
                // skip empty values
                if ($value === null) {
                    continue;
                }
                $fluentMethodCall = $this->createNextMethodCall([$value], $configVariable, $methodCallName);
                $methodCallStmts[] = new Expression($fluentMethodCall);
            }
        }
        return $methodCallStmts;
    }
    /**
     * @param mixed $value
     */
    private function createNextMethodCall($value, Variable $configVariable, string $methodCallName) : MethodCall
    {
        $args = $this->nodeFactory->createArgs($value);
        return new MethodCall($configVariable, $methodCallName, $args);
    }
    private function createConfigVariable(string $configClass) : Variable
    {
        $variableName = $this->propertyNaming->fqnToVariableName($configClass);
        return new Variable($variableName);
    }
    private function createConfigParam(string $configClass) : Param
    {
        $configVariable = $this->createConfigVariable($configClass);
        $fullyQualified = new FullyQualified($configClass);
        return new Param($configVariable, null, $fullyQualified);
    }
    private function createCamelCaseFromUnderscored(string $value) : string
    {
        $uppercaseWords = \ucwords($value, '_');
        $pascalCaseName = \str_replace('_', '', $uppercaseWords);
        return \lcfirst($pascalCaseName);
    }
}
