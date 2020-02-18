<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AssignManipulator
{
    /**
     * @var string[]
     */
    private const MODIFYING_NODES = [
        AssignOp::class,
        PreDec::class,
        PostDec::class,
        PreInc::class,
        PostInc::class,
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(NodeNameResolver $nodeNameResolver, PropertyFetchManipulator $propertyFetchManipulator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
    }

    /**
     * Checks:
     * $this->x = y;
     * $this->x[] = y;
     */
    public function isLocalPropertyAssign(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        return (bool) $this->propertyFetchManipulator->matchPropertyFetch($node->var);
    }

    /**
     * Is: "$this->value = <$value>"
     *
     * @param string[] $propertyNames
     */
    public function isLocalPropertyAssignWithPropertyNames(Node $node, array $propertyNames): bool
    {
        if (! $this->isLocalPropertyAssign($node)) {
            return false;
        }

        /** @var Assign $node */
        $propertyFetch = $this->propertyFetchManipulator->matchPropertyFetch($node->var);
        if ($propertyFetch === null) {
            return false;
        }

        return $this->nodeNameResolver->isNames($propertyFetch, $propertyNames);
    }

    /**
     * Covers:
     * - $this->propertyName = <$expr>;
     * - self::$propertyName = <$expr>;
     * - $this->propertyName[] = <$expr>;
     * - self::$propertyName[] = <$expr>;
     */
    public function matchPropertyAssignExpr(Assign $assign, string $propertyName): ?Expr
    {
        if (! $this->isLocalPropertyAssignWithPropertyNames($assign, [$propertyName])) {
            return null;
        }

        return $assign->expr;
    }

    /**
     * Matches:
     * each() = [1, 2];
     */
    public function isListToEachAssign(Assign $assign): bool
    {
        if (! $assign->expr instanceof FuncCall) {
            return false;
        }

        if (! $assign->var instanceof List_) {
            return false;
        }

        return $this->nodeNameResolver->isName($assign->expr, 'each');
    }

    public function isNodeLeftPartOfAssign(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign && $parentNode->var === $node) {
            return true;
        }

        if ($parentNode !== null && $this->isValueModifyingNode($parentNode)) {
            return true;
        }

        // traverse up to array dim fetches
        if ($parentNode instanceof ArrayDimFetch) {
            $previousParentNode = $parentNode;
            while ($parentNode instanceof ArrayDimFetch) {
                $previousParentNode = $parentNode;
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            }

            if ($parentNode instanceof Assign) {
                return $parentNode->var === $previousParentNode;
            }
        }

        return false;
    }

    private function isValueModifyingNode(Node $node): bool
    {
        foreach (self::MODIFYING_NODES as $modifyingNode) {
            if (! is_a($node, $modifyingNode)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
