<?php

declare (strict_types=1);
namespace Rector\DogFood\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer;
use Rector\DogFood\NodeManipulator\ContainerConfiguratorEmptyAssignRemover;
use Rector\DogFood\NodeManipulator\ContainerConfiguratorImportsMerger;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
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
    private const PARAMETER_NAME_TO_METHOD_CALL_MAP = [\Rector\Core\Configuration\Option::PATHS => 'paths', \Rector\Core\Configuration\Option::SKIP => 'skip', \Rector\Core\Configuration\Option::AUTOLOAD_PATHS => 'autoloadPaths', \Rector\Core\Configuration\Option::BOOTSTRAP_FILES => 'bootstrapFiles', \Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES => 'importShortClasses', \Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES => 'importNames', \Rector\Core\Configuration\Option::PARALLEL => 'parallel', \Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH => 'phpstanConfig', \Rector\Core\Configuration\Option::PHP_VERSION_FEATURES => 'phpVersion'];
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
    private const PARAMETERS_VARIABLE = 'parameters';
    /**
     * @var string
     */
    private const CONTAINER_CONFIGURATOR_CLASS = 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ContainerConfigurator';
    /**
     * @var string
     */
    private const SERVICE_CONFIGURATOR_CLASS = 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator';
    /**
     * @readonly
     * @var \Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer
     */
    private $containerConfiguratorCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\DogFood\NodeManipulator\ContainerConfiguratorEmptyAssignRemover
     */
    private $containerConfiguratorEmptyAssignRemover;
    /**
     * @readonly
     * @var \Rector\DogFood\NodeManipulator\ContainerConfiguratorImportsMerger
     */
    private $containerConfiguratorImportsMerger;
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer $containerConfiguratorCallAnalyzer, \Rector\DogFood\NodeManipulator\ContainerConfiguratorEmptyAssignRemover $containerConfiguratorEmptyAssignRemover, \Rector\DogFood\NodeManipulator\ContainerConfiguratorImportsMerger $containerConfiguratorImportsMerger, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->containerConfiguratorCallAnalyzer = $containerConfiguratorCallAnalyzer;
        $this->containerConfiguratorEmptyAssignRemover = $containerConfiguratorEmptyAssignRemover;
        $this->containerConfiguratorImportsMerger = $containerConfiguratorImportsMerger;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
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
        if (!$this->isConfigClosure($node)) {
            return null;
        }
        $this->updateClosureParam($node);
        // 1. change import of sets to single sets() method call
        $this->containerConfiguratorImportsMerger->merge($node);
        $this->traverseNodesWithCallable($node->getStmts(), function (\PhpParser\Node $node) : ?Node {
            if ($node instanceof \PhpParser\Node\Expr\Variable && $this->isName($node, 'containerConfigurator')) {
                return new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
            }
            // 2. call on rule
            if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
                $nodeVarType = $this->nodeTypeResolver->getType($node->var);
                if ($nodeVarType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $nodeVarType->getClassName() === self::SERVICE_CONFIGURATOR_CLASS) {
                    if ($this->isFoundFluentServiceCall($node)) {
                        return null;
                    }
                    $isPossiblyServiceDefinition = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($node, function (\PhpParser\Node $node) : bool {
                        return $this->isFoundFluentServiceCall($node);
                    });
                    if ($isPossiblyServiceDefinition) {
                        return null;
                    }
                }
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetConfiguredRectorRule($node)) {
                    return $this->refactorConfigureRuleMethodCall($node);
                }
                // look for "$services->set(SomeRector::Class)"
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetRectorRule($node)) {
                    $node->var = new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
                    $node->name = new \PhpParser\Node\Identifier('rule');
                    return $node;
                }
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed($node, self::PARAMETERS_VARIABLE, 'set')) {
                    return $this->refactorParameterName($node);
                }
            }
            return null;
        });
        $this->containerConfiguratorEmptyAssignRemover->removeFromClosure($node);
        return $node;
    }
    public function updateClosureParam(\PhpParser\Node\Expr\Closure $closure) : void
    {
        $param = $closure->params[0];
        if (!$param->type instanceof \PhpParser\Node\Name) {
            return;
        }
        // update closure params
        if (!$this->nodeNameResolver->isName($param->type, self::RECTOR_CONFIG_CLASS)) {
            $param->type = new \PhpParser\Node\Name\FullyQualified(self::RECTOR_CONFIG_CLASS);
        }
        if (!$this->nodeNameResolver->isName($param->var, self::RECTOR_CONFIG_VARIABLE)) {
            $param->var = new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE);
        }
    }
    public function isConfigClosure(\PhpParser\Node\Expr\Closure $closure) : bool
    {
        $params = $closure->getParams();
        if (\count($params) !== 1) {
            return \false;
        }
        $onlyParam = $params[0];
        $paramType = $onlyParam->type;
        if (!$paramType instanceof \PhpParser\Node\Name) {
            return \false;
        }
        return $this->isNames($paramType, [self::CONTAINER_CONFIGURATOR_CLASS, self::RECTOR_CONFIG_CLASS]);
    }
    private function isFoundFluentServiceCall(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $chains = $this->fluentChainMethodCallNodeAnalyzer->collectMethodCallNamesInChain($node);
        return \count($chains) > 1;
    }
    /**
     * @param Arg[] $args
     */
    private function isNonFalseOption(array $args, string $optionName) : bool
    {
        if (!$this->valueResolver->isValue($args[0]->value, $optionName)) {
            return \false;
        }
        return !$this->valueResolver->isFalse($args[1]->value);
    }
    /**
     * @return null|\PhpParser\Node\Expr\MethodCall
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
        $args = $methodCall->getArgs();
        foreach (self::PARAMETER_NAME_TO_METHOD_CALL_MAP as $parameterName => $methodName) {
            if (!$this->isNonFalseOption($args, $parameterName)) {
                continue;
            }
            $args = $this->valueResolver->isTrueOrFalse($args[1]->value) ? [] : [$args[1]];
            return new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE), $methodName, $args);
        }
        return null;
    }
}
