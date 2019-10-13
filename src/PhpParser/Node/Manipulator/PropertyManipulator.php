<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

/**
 * "private $property"
 */
final class PropertyManipulator
{
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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NameResolver $nameResolver,
        ParsedNodesByType $parsedNodesByType
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->parsedNodesByType = $parsedNodesByType;
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

        $nodesToSearch = $this->parsedNodesByType->findUsedTraitsInClass($classNode);
        $nodesToSearch[] = $classNode;

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($nodesToSearch, function (Node $node) use ($property) {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return null;
            }

            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $property)) {
                return null;
            }

            // is it the name match?
            if (! $this->nameResolver->areNamesEqual($node, $property)) {
                return null;
            }

            return $node;
        });

        return $propertyFetches;
    }
}
