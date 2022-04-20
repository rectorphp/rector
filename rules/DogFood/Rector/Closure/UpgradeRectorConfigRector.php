<?php

declare (strict_types=1);
namespace Rector\DogFood\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\UpgradeRectorConfigRectorTest
 */
final class UpgradeRectorConfigRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const PARAMETER_NAME_TO_METHOD_CALL_MAP = [\Rector\Core\Configuration\Option::AUTOLOAD_PATHS => 'autoloadPaths', \Rector\Core\Configuration\Option::BOOTSTRAP_FILES => 'bootstrapFiles', \Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES => 'importNames', \Rector\Core\Configuration\Option::PARALLEL => 'parallel', \Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH => 'phpstanConfig', \Rector\Core\Configuration\Option::PHP_VERSION_FEATURES => 'phpVersion'];
    /**
     * @var string
     */
    private const RECTOR_CONFIG_VARIABLE = 'rectorConfig';
    /**
     * @var string
     */
    private const RECTOR_CONFIG_CLASS = 'Rector\\Config\\RectorConfig';
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';
    /**
     * @readonly
     * @var \Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer
     */
    private $containerConfiguratorCallAnalyzer;
    public function __construct(\Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer $containerConfiguratorCallAnalyzer)
    {
        $this->containerConfiguratorCallAnalyzer = $containerConfiguratorCallAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Upgrade rector.php config to use of RectorConfig', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();

    $rectorConfig->rule(TypedPropertyRector::class);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $params = $node->getParams();
        if (\count($params) !== 1) {
            return null;
        }
        $onlyParam = $params[0];
        $paramType = $onlyParam->type;
        if (!$paramType instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (!$this->isNames($paramType, ['Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ContainerConfigurator', self::RECTOR_CONFIG_CLASS])) {
            return null;
        }
        // update closure params
        if (!$this->nodeNameResolver->isName($paramType, self::RECTOR_CONFIG_CLASS)) {
            $onlyParam->type = new \PhpParser\Node\Name\FullyQualified(self::RECTOR_CONFIG_CLASS);
        }
        if (!$this->nodeNameResolver->isName($onlyParam->var, self::RECTOR_CONFIG_VARIABLE)) {
            $onlyParam->var = new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
        }
        $this->traverseNodesWithCallable($node->getStmts(), function (\PhpParser\Node $node) : ?MethodCall {
            // 1. call on rule
            if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetConfiguredRectorRule($node)) {
                    return $this->refactorConfigureRuleMethodCall($node);
                }
                // look for "$services->set(SomeRector::Class)"
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetRectorRule($node)) {
                    $node->var = new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
                    $node->name = new \PhpParser\Node\Identifier('rule');
                    return $node;
                }
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node, self::PARAMETERS, 'set')) {
                    return $this->refactorParameterName($node);
                }
            }
            // look for "$services = $containerConfigurator->services()"
            $this->removeHelperAssigns($node);
            return null;
        });
        // change the node
        return $node;
    }
    public function removeHelperAssigns(\PhpParser\Node $node) : void
    {
        if (!$node instanceof \PhpParser\Node\Expr\Assign) {
            return;
        }
        if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node->expr, 'containerConfigurator', 'services')) {
            $this->removeNode($node);
        }
        if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node->expr, self::RECTOR_CONFIG_VARIABLE, 'services')) {
            $this->removeNode($node);
        }
        if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node->expr, 'containerConfigurator', self::PARAMETERS)) {
            $this->removeNode($node);
        }
        if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node->expr, self::RECTOR_CONFIG_VARIABLE, self::PARAMETERS)) {
            $this->removeNode($node);
        }
    }
    /**
     * @param Arg[] $args
     */
    private function isOptionWithTrue(array $args, string $optionName) : bool
    {
        if (!$this->valueResolver->isValue($args[0]->value, $optionName)) {
            return \false;
        }
        return $this->valueResolver->isTrue($args[1]->value);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorConfigureRuleMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $caller = $methodCall->var;
        if (!$caller instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetRectorRule($caller)) {
            return null;
        }
        $methodCall->var = new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
        $methodCall->name = new \PhpParser\Node\Identifier('ruleWithConfiguration');
        $methodCall->args = \array_merge($caller->getArgs(), $methodCall->getArgs());
        return $methodCall;
    }
    private function refactorParameterName(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        foreach (self::PARAMETER_NAME_TO_METHOD_CALL_MAP as $parameterName => $methodName) {
            if (!$this->isOptionWithTrue($methodCall->getArgs(), $parameterName)) {
                continue;
            }
            return new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE), $methodName);
        }
        return null;
    }
}
