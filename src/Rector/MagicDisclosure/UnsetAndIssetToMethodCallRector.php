<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;

/**
 * __isset/__unset to specific call
 *
 * Example - from:
 * - isset($container['someKey'])
 * - unset($container['someKey'])
 *
 * To
 * - $container->hasService('someKey');
 * - $container->removeService('someKey');
 */
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * Type to method call()
     *
     * @param string[][][] $typeToMethodCalls
     */
    public function __construct(
        array $typeToMethodCalls,
        MethodCallNodeFactory $methodCallNodeFactory,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * Detects "isset($value['someKey']);"
     * or "unset($value['someKey']);"
     */
    public function isCandidate(Node $node): bool
    {
        $this->activeTransformation = [];

        if (! $node instanceof Isset_ && ! $node instanceof Unset_) {
            return false;
        }

        foreach ($node->vars as $var) {
            if (! $var instanceof ArrayDimFetch) {
                continue;
            }

            if ($this->matchArrayDimFetch($var)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Isset_|Unset_ $issetOrUnsetNode
     */
    public function refactor(Node $issetOrUnsetNode): ?Node
    {
        $method = $this->resolveMethod($issetOrUnsetNode);
        if ($method === null) {
            return $issetOrUnsetNode;
        }

        /** @var ArrayDimFetch $arrayDimFetchNode */
        $arrayDimFetchNode = $issetOrUnsetNode->vars[0];

        /** @var Variable $variableNode */
        $variableNode = $arrayDimFetchNode->var;

        $key = $arrayDimFetchNode->dim;

        $methodCall = $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $variableNode,
            $method,
            [$key]
        );

        if ($issetOrUnsetNode instanceof Unset_) {
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
        $variableNodeTypes = $this->nodeTypeResolver->resolve($arrayDimFetchNode->var);

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (in_array($type, $variableNodeTypes, true)) {
                $this->activeTransformation = $transformation;

                return true;
            }
        }

        return false;
    }
}
