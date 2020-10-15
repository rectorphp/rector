<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * All parsed nodes grouped type
 */
final class ParsedPropertyFetchNodeCollector
{
    /**
     * @var PropertyFetch[][][]
     */
    private $propertyFetchesByTypeAndName = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * To prevent circular reference
     * @required
     */
    public function autowireParsedPropertyFetchNodeCollector(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function collect(Node $node): void
    {
        if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
            return;
        }

        $propertyType = $this->resolvePropertyCallerType($node);

        // make sure name is valid
        if ($node->name instanceof StaticCall || $node->name instanceof MethodCall) {
            return;
        }

        $propertyName = $this->nodeNameResolver->getName($node->name);
        if ($propertyName === null) {
            return;
        }

        $this->addPropertyFetchWithTypeAndName($propertyType, $node, $propertyName);
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByTypeAndName(string $className, string $propertyName): array
    {
        return $this->propertyFetchesByTypeAndName[$className][$propertyName] ?? [];
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    private function resolvePropertyCallerType(Node $node): Type
    {
        if ($node instanceof PropertyFetch) {
            return $this->nodeTypeResolver->getStaticType($node->var);
        }

        return $this->nodeTypeResolver->getStaticType($node->class);
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyFetchNode
     */
    private function addPropertyFetchWithTypeAndName(
        Type $propertyType,
        Node $propertyFetchNode,
        string $propertyName
    ): void {
        if ($propertyType instanceof TypeWithClassName) {
            $this->propertyFetchesByTypeAndName[$propertyType->getClassName()][$propertyName][] = $propertyFetchNode;
        }

        if ($propertyType instanceof UnionType) {
            foreach ($propertyType->getTypes() as $unionedType) {
                $this->addPropertyFetchWithTypeAndName($unionedType, $propertyFetchNode, $propertyName);
            }
        }
    }
}
