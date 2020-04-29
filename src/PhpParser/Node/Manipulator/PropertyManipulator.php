<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Doctrine\AbstractRector\DoctrineTrait;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * "private $property"
 */
final class PropertyManipulator
{
    use DoctrineTrait;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var ClassLikeParsedNodesFinder
     */
    private $classLikeParsedNodesFinder;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        AssignManipulator $assignManipulator,
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->assignManipulator = $assignManipulator;
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
    }

    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    public function getAllPropertyFetch(Property $property): array
    {
        /** @var Class_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        $nodesToSearch = $this->classLikeParsedNodesFinder->findUsedTraitsInClass($classNode);
        $nodesToSearch[] = $classNode;

        $singleProperty = $property->props[0];

        return $this->betterNodeFinder->find($nodesToSearch, function (Node $node) use (
            $singleProperty,
            $nodesToSearch
        ): bool {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return false;
            }

            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $singleProperty)) {
                return false;
            }

            // is it the name match?
            if (! $this->nodeNameResolver->areNamesEqual($node, $singleProperty)) {
                return false;
            }

            return in_array($node->getAttribute(AttributeKey::CLASS_NODE), $nodesToSearch, true);
        });
    }

    public function isReadOnlyProperty(Property $property): bool
    {
        foreach ($this->getAllPropertyFetch($property) as $propertyFetch) {
            if (! $this->isReadContext($propertyFetch)) {
                return false;
            }
        }

        return true;
    }

    public function isPropertyUsedInReadContext(Property $property): bool
    {
        if ($this->isDoctrineProperty($property)) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo !== null && $phpDocInfo->hasByType(SerializerTypeTagValueNode::class)) {
            return true;
        }

        foreach ($this->getAllPropertyFetch($property) as $propertyFetch) {
            if ($this->isReadContext($propertyFetch)) {
                return true;
            }
        }

        // has class $this->$variable call?
        /** @var ClassLike $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        return (bool) $this->betterNodeFinder->findFirst($class->stmts, function (Node $node): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            if (! $this->isReadContext($node)) {
                return false;
            }
            return $node->name instanceof Expr;
        });
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    private function isReadContext(Node $node): bool
    {
        return ! $this->assignManipulator->isNodeLeftPartOfAssign($node);
    }
}
