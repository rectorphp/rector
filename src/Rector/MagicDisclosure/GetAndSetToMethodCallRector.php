<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
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
     * @var string
     */
    private const FETCH_TYPE_GET = 'get';

    /**
     * @var string
     */
    private const FETCH_TYPE_SET = 'set';

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
    private $fetchType;

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
        $this->fetchType = null;
        $this->activeTransformation = [];

        $propertyFetchNode = $this->resolvePropertyFetch($node);
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
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        if ($this->fetchType === self::FETCH_TYPE_GET) {
            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $expressionNode->expr->expr;

            $expressionNode->expr->expr = $this->createMethodCallNodeFromPropertyFetchNode($propertyFetchNode);
        }

        if ($this->fetchType === self::FETCH_TYPE_SET) {
            /** @var Assign $assignNode */
            $assignNode = $expressionNode->expr;

            $expressionNode->expr = $this->createMethodCallNodeFromAssignNode($assignNode);
        }

        return $expressionNode;
    }

    private function createMethodCallNodeFromPropertyFetchNode(PropertyFetch $propertyFetchNode): MethodCall
    {
        $value = $propertyFetchNode->name->name;

        $method = $this->activeTransformation[$this->fetchType] ?? null;

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

        $method = $this->activeTransformation[$this->fetchType] ?? null;

        return $this->nodeFactory->createMethodCallWithVariableAndArguments(
            $propertyFetchNode->var,
            $method,
            [$key, $assignNode->expr]
        );
    }

    private function resolvePropertyFetch(Node $node): ?PropertyFetch
    {
        if (! $node instanceof Expression) {
            return null;
        }

        if (! $node->expr instanceof Assign) {
            return null;
        }

        return $this->resolvePropertyFetchNodeFromAssignNode($node->expr);
    }

    private function resolvePropertyFetchNodeFromAssignNode(Assign $assignNode): ?PropertyFetch
    {
        if ($assignNode->expr instanceof PropertyFetch) {
            $this->fetchType = self::FETCH_TYPE_GET;

            return $assignNode->expr;
        }

        if ($assignNode->var instanceof PropertyFetch) {
            $this->fetchType = self::FETCH_TYPE_SET;

            return $assignNode->var;
        }

        return null;
    }
}
