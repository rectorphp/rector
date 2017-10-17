<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * __set to specific call
 *
 * Example - from:
 * - $container->someKey = 'someValue'
 *
 * To
 * - $container->setValue('someKey', 'someValue');
 */
final class SetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyAccessAnalyzer;

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
        PropertyFetchAnalyzer $propertyAccessAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->propertyAccessAnalyzer = $propertyAccessAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Detects "$this->someProperty = '...';"
     */
    public function isCandidate(Node $node): bool
    {
        $this->activeMethod = null;

        if (! $node instanceof Expression) {
            return false;
        }

        if (! $node->expr instanceof Assign) {
            return false;
        }

        $possiblePropertyFetchNode = $node->expr->var;

        foreach ($this->typeToMethodCalls as $type => $method) {
            if ($this->propertyAccessAnalyzer->isMagicPropertyFetchOnType($possiblePropertyFetchNode, $type)) {
                $this->activeMethod = $method;

                return true;
            }
        }

        return false;
    }

    /**
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $expressionNode->expr->var;

        $methodCall = $this->nodeFactory->createMethodCallWithVariable($propertyFetchNode->var, $this->activeMethod);

        $keyName = $propertyFetchNode->name->name;

        $methodCall->args[] = $this->nodeFactory->createArg($keyName);
        $methodCall->args[] = $this->nodeFactory->createArg($expressionNode->expr->expr);

        $expressionNode->expr = $methodCall;

        return $expressionNode;
    }
}
