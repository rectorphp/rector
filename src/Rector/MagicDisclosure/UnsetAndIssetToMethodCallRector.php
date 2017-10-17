<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
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
     * @var string[]
     */
    private $typeToMethodCalls = [];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var mixed
     */
    private $activeTransformation;

    /**
     * Type to method call()
     *
     * @param string[] $typeToMethodCalls
     */
    public function __construct(
        array $typeToMethodCalls,
        NodeFactory $nodeFactory
    ) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Detects "isset($value['someKey']);"
     * or "unset($value['someKey']);"
     */
    public function isCandidate(Node $node): bool
    {
        $this->activeTransformation = null;

        if (! $node instanceof Isset_ && ! $node instanceof Unset_) {
            return false;
        }

        /** @var ArrayDimFetch $var */
        foreach ($node->vars as $var) {
            $variableNode = $var->var;
            $variableNodeType = $variableNode->getAttribute(Attribute::TYPE);

            foreach ($this->typeToMethodCalls as $type => $transformation) {
                if ($variableNodeType === $type) {
                    $this->activeTransformation = $transformation;

                    return true;
                }
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

        $variableNode = $issetOrUnsetNode->vars[0]->var;
        $key = $issetOrUnsetNode->vars[0]->dim;

        $methodCall = $this->nodeFactory->createMethodCallWithVariableAndArguments(
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
}
