<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class BetterNodeFinder
{
    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(NodeFinder $nodeFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->nodeFinder = $nodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param string|string[] $type
     */
    public function findFirstParentInstanceOf(Node $node, $type): ?Node
    {
        if (! is_array($type)) {
            $type = [$type];
        }

        /** @var Node|null $parentNode */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($parentNode === null) {
            return null;
        }

        do {
            if ($this->isTypes($parentNode, $type)) {
                return $parentNode;
            }

            if ($parentNode === null) {
                return null;
            }
        } while ($parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE));

        return null;
    }

    public function findFirstAncestorInstanceOf(Node $node, string $type): ?Node
    {
        $currentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($currentNode !== null) {
            if ($currentNode instanceof $type) {
                return $currentNode;
            }

            $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        return null;
    }

    /**
     * @param Node|Node[]|Stmt[] $nodes
     * @return Node[]
     */
    public function findInstanceOf($nodes, string $type): array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirstInstanceOf($nodes, string $type): ?Node
    {
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findLastInstanceOf($nodes, string $type): ?Node
    {
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if ($foundInstances === []) {
            return null;
        }

        return array_pop($foundInstances);
    }

    /**
     * @param Node|Node[] $nodes
     * @return Node[]
     */
    public function find($nodes, callable $filter): array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirst($nodes, callable $filter): ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }

    public function findFirstPrevious(Node $node, callable $filter): ?Node
    {
        $node = $node instanceof Expression ? $node : $node->getAttribute(AttributeKey::CURRENT_EXPRESSION);
        if ($node === null) {
            return null;
        }

        $foundNode = $this->findFirst([$node], $filter);
        // we found what we need
        if ($foundNode !== null) {
            return $foundNode;
        }

        // move to next expression
        $previousExpression = $node->getAttribute(AttributeKey::PREVIOUS_EXPRESSION);
        if ($previousExpression === null) {
            return null;
        }

        return $this->findFirstPrevious($previousExpression, $filter);
    }

    /**
     * @return Assign[]
     */
    public function findAssignsOfVariable(Node $node, Variable $variable): array
    {
        $assignNodes = $this->findInstanceOf($node, Assign::class);

        return array_filter($assignNodes, function (Assign $assign) use ($variable): bool {
            if ($this->betterStandardPrinter->areNodesEqual($assign->var, $variable)) {
                return true;
            }

            if ($assign->var instanceof ArrayDimFetch) {
                return $this->betterStandardPrinter->areNodesEqual($assign->var->var, $variable);
            }

            return false;
        });
    }

    /**
     * @param string[] $types
     */
    private function isTypes(Node $node, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($node, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
