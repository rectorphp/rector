<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * __get to specific call
 *
 * Example - from:
 * - $someService = $container->someService;
 * - $container->someService = $someService;
 *
 * To
 * - $container->getService('someService');
 * - $container->setService('someService', $someService);
 */
final class GetAndSetToMethodCallRector extends AbstractRector
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
    private $type;

    /**
     * @var string[]
     */
    private $activeTransformation = [];

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

    public function isCandidate(Node $node): bool
    {
        $this->type = null;
        $this->activeTransformation = [];

        if (! $node instanceof Expression) {
            return false;
        }

        if (! $node->expr instanceof Assign) {
            return false;
        }

        $propertyFetchNode = $this->resolvePropertyFetchNodeFromAssignNode($node->expr);
        if ($propertyFetchNode === null) {
            return false;
        }

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if ($this->propertyAccessAnalyzer->isMagicPropertyFetchOnType($propertyFetchNode, $type)) {
                $this->activeTransformation = $transformation;

                return true;
            }
        }

        return false;
    }

    /**
     * @param Expr $exprNode
     */
    public function refactor(Node $exprNode): ?Node
    {
        if ($this->type === 'get') {
            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $exprNode->expr->expr;

            $exprNode->expr->expr = $this->createMethodCallNodeFromPropertyFetchNode($propertyFetchNode);
        }

        if ($this->type === 'set') {
            /** @var Assign $assignNode */
            $assignNode = $exprNode->expr;

            $exprNode->expr = $this->createMethodCallNodeFromAssignNode($assignNode);
        }

        return $exprNode;
    }

    private function createMethodCallNodeFromPropertyFetchNode(PropertyFetch $propertyFetchNode): MethodCall
    {
        $value = $propertyFetchNode->name->name;

        $method = $this->activeTransformation[$this->type] ?? null;

        $methodCall = $this->nodeFactory->createMethodCallWithVariableAndArguments(
            $propertyFetchNode->var,
            $method,
            [$value]
        );

        return $methodCall;
    }

    private function createMethodCallNodeFromAssignNode(Assign $assignNode): MethodCall
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->var;

        $key = $propertyFetchNode->name->name;

        $method = $this->activeTransformation[$this->type] ?? null;

        return $this->nodeFactory->createMethodCallWithVariableAndArguments(
            $propertyFetchNode->var,
            $method,
            [$key, $assignNode->expr]
        );
    }

    private function resolvePropertyFetchNodeFromAssignNode(Assign $assignNode): ?PropertyFetch
    {
        if ($assignNode->expr instanceof PropertyFetch) {
            $this->type = 'get';

            return $assignNode->expr;
        }

        if ($assignNode->var instanceof PropertyFetch) {
            $this->type = 'set';

            return $assignNode->var;
        }

        return null;
    }
}
