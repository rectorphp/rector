<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use Nette\DI\Container;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UnsetAndIssetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var mixed[]
     */
    private $activeTransformation = [];

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * Type to method call()
     *
     * @param string[][][] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [
            new ConfiguredCodeSample(
                'isset($container["someKey"]);',
                '$container->hasService("someKey");',
                [
                    '$typeToMethodCalls' => [
                        Container::class => [
                            'isset' => 'hasService',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                'unset($container["someKey"])',
                '$container->removeService("someKey");',
                [
                    [
                        '$typeToMethodCalls' => [
                            Container::class => [
                                'unset' => 'removeService',
                            ],
                        ],
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
        return [Isset_::class, Unset_::class];
    }

    /**
     * @param Isset_|Unset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->activeTransformation = [];

        foreach ($node->vars as $var) {
            if (! $var instanceof ArrayDimFetch) {
                continue;
            }

            if (! $this->matchArrayDimFetch($var)) {
                return null;
            }
        }

        $method = $this->resolveMethod($node);
        if ($method === null) {
            return null;
        }

        /** @var ArrayDimFetch $arrayDimFetchNode */
        $arrayDimFetchNode = $node->vars[0];

        /** @var Variable $variableNode */
        $variableNode = $arrayDimFetchNode->var;

        $key = $arrayDimFetchNode->dim;

        $methodCall = $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $variableNode,
            $method,
            [$key]
        );

        if ($node instanceof Unset_) {
            // wrap it, so add ";" in the end of line
            return new Expression($methodCall);
        }

        return $methodCall;
    }

    /**
     * @param Isset_|Unset_ $issetOrUnsetNode
     */
    private function resolveMethod(Node $issetOrUnsetNode): ?string
    {
        if ($issetOrUnsetNode instanceof Isset_) {
            return $this->activeTransformation['isset'] ?? null;
        }

        if ($issetOrUnsetNode instanceof Unset_) {
            return $this->activeTransformation['unset'] ?? null;
        }

        return null;
    }

    private function matchArrayDimFetch(ArrayDimFetch $arrayDimFetchNode): bool
    {
        $varNodeTypes = $this->getTypes($arrayDimFetchNode->var);

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (in_array($type, $varNodeTypes, true)) {
                $this->activeTransformation = $transformation;

                return true;
            }
        }

        return false;
    }
}
