<?php

declare(strict_types=1);

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
use Rector\DogFood\NodeAnalyzer\ContainerConfiguratorCallAnalyzer;
use Rector\DogFood\NodeManipulator\ContainerConfiguratorEmptyAssignRemover;
use Rector\DogFood\NodeManipulator\ContainerConfiguratorImportsMerger;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\UpgradeRectorConfigRectorTest
 */
final class UpgradeRectorConfigRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const PARAMETER_NAME_TO_METHOD_CALL_MAP = [
        Option::AUTOLOAD_PATHS => 'autoloadPaths',
        Option::BOOTSTRAP_FILES => 'bootstrapFiles',
        Option::AUTO_IMPORT_NAMES => 'importNames',
        Option::PARALLEL => 'parallel',
        Option::PHPSTAN_FOR_RECTOR_PATH => 'phpstanConfig',
        Option::PHP_VERSION_FEATURES => 'phpVersion',
    ];

    /**
     * @var string
     */
    private const RECTOR_CONFIG_VARIABLE = 'rectorConfig';

    /**
     * @var string
     */
    private const RECTOR_CONFIG_CLASS = 'Rector\Config\RectorConfig';

    /**
     * @var string
     */
    private const PARAMETERS_VARIABLE = 'parameters';

    public function __construct(
        private readonly ContainerConfiguratorCallAnalyzer $containerConfiguratorCallAnalyzer,
        private readonly ContainerConfiguratorEmptyAssignRemover $containerConfiguratorEmptyAssignRemover,
        private readonly ContainerConfiguratorImportsMerger $containerConfiguratorImportsMerger
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Upgrade rector.php config to use of RectorConfig', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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

                ,
                <<<'CODE_SAMPLE'
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();

    $rectorConfig->rule(TypedPropertyRector::class);
};
CODE_SAMPLE
            ),
        ]);
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
        $params = $node->getParams();
        if (count($params) !== 1) {
            return null;
        }

        $onlyParam = $params[0];
        $paramType = $onlyParam->type;
        if (! $paramType instanceof Name) {
            return null;
        }

        if (! $this->isNames($paramType, [
            'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
            self::RECTOR_CONFIG_CLASS,
        ])) {
            return null;
        }

        $this->updateClosureParam($onlyParam);

        // 1. change import of sets to single sets() method call
        $this->containerConfiguratorImportsMerger->merge($node);

        $this->traverseNodesWithCallable($node->getStmts(), function (Node $node): ?MethodCall {
            // 2. call on rule
            if ($node instanceof MethodCall) {
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetConfiguredRectorRule($node)) {
                    return $this->refactorConfigureRuleMethodCall($node);
                }

                // look for "$services->set(SomeRector::Class)"
                if ($this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetRectorRule($node)) {
                    $node->var = new Variable(self::RECTOR_CONFIG_VARIABLE);
                    $node->name = new Identifier('rule');

                    return $node;
                }

                if ($this->containerConfiguratorCallAnalyzer->isMethodCallNamed(
                    $node,
                    self::PARAMETERS_VARIABLE,
                    'set'
                )) {
                    return $this->refactorParameterName($node);
                }
            }

            return null;
        });

        $this->containerConfiguratorEmptyAssignRemover->removeFromClosure($node);

        return $node;
    }

    public function updateClosureParam(Param $param): void
    {
        if (! $param->type instanceof Name) {
            return;
        }

        // update closure params
        if (! $this->nodeNameResolver->isName($param->type, self::RECTOR_CONFIG_CLASS)) {
            $param->type = new FullyQualified(self::RECTOR_CONFIG_CLASS);
        }

        if (! $this->nodeNameResolver->isName($param->var, self::RECTOR_CONFIG_VARIABLE)) {
            $param->var = new Variable(self::RECTOR_CONFIG_VARIABLE);
        }
    }

    /**
     * @param Arg[] $args
     */
    private function isOptionWithTrue(array $args, string $optionName): bool
    {
        if (! $this->valueResolver->isValue($args[0]->value, $optionName)) {
            return false;
        }

        return $this->valueResolver->isTrue($args[1]->value);
    }

    private function refactorConfigureRuleMethodCall(MethodCall $methodCall): null|MethodCall
    {
        $caller = $methodCall->var;

        if (! $caller instanceof MethodCall) {
            return null;
        }

        if (! $this->containerConfiguratorCallAnalyzer->isMethodCallWithServicesSetRectorRule($caller)) {
            return null;
        }

        $methodCall->var = new Variable(self::RECTOR_CONFIG_VARIABLE);

        $methodCall->name = new Identifier('ruleWithConfiguration');
        $methodCall->args = array_merge($caller->getArgs(), $methodCall->getArgs());

        return $methodCall;
    }

    private function refactorParameterName(MethodCall $methodCall): ?MethodCall
    {
        foreach (self::PARAMETER_NAME_TO_METHOD_CALL_MAP as $parameterName => $methodName) {
            if (! $this->isOptionWithTrue($methodCall->getArgs(), $parameterName)) {
                continue;
            }

            return new MethodCall(new Variable(self::RECTOR_CONFIG_VARIABLE), $methodName);
        }

        return null;
    }
}
