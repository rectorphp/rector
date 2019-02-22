<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Node\NodeToStringTypeResolver;
use Rector\NodeTypeResolver\Php\VarTypeInfo;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ComplexNodeTypeResolver
{
    /**
     * @var NodeToStringTypeResolver
     */
    private $nodeToStringTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    public function __construct(
        NodeToStringTypeResolver $nodeToStringTypeResolver,
        NameResolver $nameResolver,
        BetterNodeFinder $betterNodeFinder,
        NodeTypeAnalyzer $nodeTypeAnalyzer
    ) {
        $this->nodeToStringTypeResolver = $nodeToStringTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    /**
     * Based on static analysis of code, looking for property assigns
     */
    public function resolvePropertyTypeInfo(Property $property): ?VarTypeInfo
    {
        $types = [];

        $propertyDefault = $property->props[0]->default;
        if ($propertyDefault !== null) {
            $types[] = $this->nodeToStringTypeResolver->resolver($propertyDefault);
        }

        $classNode = $property->getAttribute(Attribute::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $propertyName = $this->nameResolver->resolve($property);
        if ($propertyName === null) {
            return null;
        }

        /** @var Assign[] $propertyAssignNodes */
        $propertyAssignNodes = $this->betterNodeFinder->find([$classNode], function (Node $node) use (
            $propertyName
        ): bool {
            if ($node instanceof Assign && $node->var instanceof PropertyFetch) {
                // is property match
                return $this->nameResolver->isName($node->var, $propertyName);
            }

            return false;
        });

        foreach ($propertyAssignNodes as $propertyAssignNode) {
            $types = array_merge(
                $types,
                $this->nodeTypeAnalyzer->resolveSingleTypeToStrings($propertyAssignNode->expr)
            );
        }

        $types = array_filter($types);

        return new VarTypeInfo($types, $types, true);
    }
}
