<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Doctrine\AbstractRector\DoctrineTrait;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

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
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NameResolver $nameResolver,
        ParsedNodesByType $parsedNodesByType,
        DocBlockManipulator $docBlockManipulator,
        AssignManipulator $assignManipulator
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->assignManipulator = $assignManipulator;
    }

    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    public function getAllPropertyFetch(PropertyProperty $propertyProperty): array
    {
        /** @var Class_|null $classNode */
        $classNode = $propertyProperty->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        $nodesToSearch = $this->parsedNodesByType->findUsedTraitsInClass($classNode);
        $nodesToSearch[] = $classNode;

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($nodesToSearch, function (Node $node) use (
            $propertyProperty,
            $nodesToSearch
        ): bool {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return false;
            }

            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $propertyProperty)) {
                return false;
            }

            // is it the name match?
            if (! $this->nameResolver->areNamesEqual($node, $propertyProperty)) {
                return false;
            }
            return in_array($node->getAttribute(AttributeKey::CLASS_NODE), $nodesToSearch, true);
        });

        return $propertyFetches;
    }

    public function isPropertyUsedInReadContext(PropertyProperty $propertyProperty): bool
    {
        $property = $this->getProperty($propertyProperty);

        if ($this->isDoctrineProperty($property)) {
            return true;
        }

        if ($this->docBlockManipulator->hasTag($property, SerializerTypeTagValueNode::class)) {
            return true;
        }

        foreach ($this->getAllPropertyFetch($propertyProperty) as $propertyFetch) {
            if ($this->isReadContext($propertyFetch)) {
                return true;
            }
        }

        // has class $this->$variable call?
        /** @var Node\Stmt\ClassLike $class */
        $class = $propertyProperty->getAttribute(AttributeKey::CLASS_NODE);
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

    public function isPrivate(PropertyProperty $propertyProperty): bool
    {
        return $this->getProperty($propertyProperty)->isPrivate();
    }

    private function getProperty(PropertyProperty $propertyProperty): Property
    {
        $property = $propertyProperty->getAttribute(AttributeKey::PARENT_NODE);

        if (! $property instanceof Property) {
            throw new ShouldNotHappenException('PropertyProperty should always have Property as parent');
        }

        return $property;
    }

    /**
     * @param PropertyFetch|Expr\StaticPropertyFetch $node
     */
    private function isReadContext(Node $node): bool
    {
        return ! $this->assignManipulator->isNodeLeftPartOfAssign($node);
    }
}
