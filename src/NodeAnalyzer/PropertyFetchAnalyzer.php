<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Broker
     */
    private $broker;

    public function __construct(NodeTypeResolver $nodeTypeResolver, Broker $broker)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->broker = $broker;
    }

    public function isTypeAndProperty(Node $node, string $type, string $property): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var PropertyFetch $node */
        $propertyFetchName = (string) $node->name;

        return $propertyFetchName === $property;
    }

    public function isMagicOnType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);
        if (! in_array($type, $varNodeTypes, true)) {
            return false;
        }

        return ! $this->hasPublicProperty($node, (string) $node->name);
    }

    /**
     * @param string[] $properties
     */
    public function isProperties(Node $node, array $properties): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        return in_array($identifierNode->toString(), $properties, true);
    }

    /**
     * @param string[] $types
     */
    public function isTypes(Node $node, array $types): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return (bool) array_intersect($varNodeTypes, $types);
    }

    /**
     * @param string[] $propertyNames
     */
    public function isTypeAndProperties(Node $node, string $type, array $propertyNames): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var PropertyFetch $node */
        $propertyFetchName = (string) $node->name;

        return in_array($propertyFetchName, $propertyNames, true);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): array
    {
        if (! $this->isTypes($node, $types)) {
            return [];
        }

        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $node;

        return $this->nodeTypeResolver->resolve($propertyFetchNode->var);
    }

    private function hasPublicProperty(PropertyFetch $node, string $propertyName): bool
    {
        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        $propertyFetchType = $nodeScope->getType($node->var);
        if ($propertyFetchType instanceof ObjectType) {
            $propertyFetchType = $propertyFetchType->getClassName();
        }

        if (! $this->broker->hasClass($propertyFetchType)) {
            return false;
        }

        $classReflection = $this->broker->getClass($propertyFetchType);
        if (! $classReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $classReflection->getProperty($propertyName, $nodeScope);

        return $propertyReflection->isPublic();
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return in_array($type, $varNodeTypes, true);
    }
}
