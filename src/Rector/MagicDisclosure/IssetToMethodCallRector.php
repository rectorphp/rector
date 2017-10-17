<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * __isset to specific call
 *
 * Example - from:
 * - isset($container['someKey'])
 *
 * To
 * - $container->hasService('someKey');
 */
final class IssetToMethodCallRector extends AbstractRector
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
     * @var string
     */
    private $activeMethod;

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
     */
    public function isCandidate(Node $node): bool
    {
        $this->activeMethod = null;

        if (! $node instanceof Isset_) {
            return false;
        }

        foreach ($node->vars as $var) {
            if (! $var instanceof ArrayDimFetch) {
                continue;
            }

            $variableNode = $var->var;

            foreach ($this->typeToMethodCalls as $type => $method) {
                $variableNodeType = $variableNode->getAttribute(Attribute::TYPE);
                if ($variableNodeType === $type) {
                    $this->activeMethod = $method;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Isset_ $issetNode
     */
    public function refactor(Node $issetNode): ?Node
    {
        $variableNode = $issetNode->vars[0]->var;
        $key = $issetNode->vars[0]->dim;

        return $this->nodeFactory->createMethodCallWithVariableAndArguments(
            $variableNode,
            $this->activeMethod,
            [$key]
        );
    }
}
