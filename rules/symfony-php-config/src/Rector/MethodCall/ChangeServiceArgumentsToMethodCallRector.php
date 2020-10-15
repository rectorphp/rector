<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SymfonyPhpConfig\NodeAnalyzer\SymfonyPhpConfigClosureAnalyzer;

/**
 * @see \Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector\ChangeServiceArgumentsToMethodCallRectorTest
 */
final class ChangeServiceArgumentsToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CLASS_TYPE_TO_METHOD_NAME = 'class_type_to_method_name';

    /**
     * @var mixed[]
     */
    private $classTypeToMethodName = [];

    /**
     * @var SymfonyPhpConfigClosureAnalyzer
     */
    private $symfonyPhpConfigClosureAnalyzer;

    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    public function __construct(
        SymfonyPhpConfigClosureAnalyzer $symfonyPhpConfigClosureAnalyzer,
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer
    ) {
        $this->symfonyPhpConfigClosureAnalyzer = $symfonyPhpConfigClosureAnalyzer;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $service->arg(...) to $service->call(...)', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value
        ]]);
}
CODE_SAMPLE
,
                [
                    self::CLASS_TYPE_TO_METHOD_NAME => [
                        'SomeClass' => 'configure',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $idValue = $this->getValue($node->args[0]->value);

        foreach ($this->classTypeToMethodName as $classType => $methodName) {
            if (! is_a($idValue, $classType, true)) {
                continue;
            }

            // collect all arg() or args() value
            $arrayItems = [];
            $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($node);
            foreach ($chainMethodCalls as $chainMethodCall) {
                // @todo support "args" too
                // support multi args too
                if ($this->isName($chainMethodCall->name, 'arg')) {
                    $arrayItems[] = new ArrayItem($chainMethodCall->args[1]->value, $chainMethodCall->args[0]->value);

                    $chainMethodCall->var = $chainMethodCall;
                    $chainMethodCall->name = new Identifier('call');

                    $array = [new String_($methodName), $this->createArray([new Array_($arrayItems)])];
                    $chainMethodCall->args = $this->createArgs($array);
                }
            }
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->classTypeToMethodName = $configuration[self::CLASS_TYPE_TO_METHOD_NAME] ?? [];
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        $closure = $methodCall->getAttribute(AttributeKey::CLOSURE_NODE);
        if ($closure === null) {
            return true;
        }

        if (! $this->symfonyPhpConfigClosureAnalyzer->isPhpConfigClosure($closure)) {
            return true;
        }

        return ! $this->isName($methodCall->name, 'set');
    }
}
