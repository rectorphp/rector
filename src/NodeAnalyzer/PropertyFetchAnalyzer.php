<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionProperty;

/**
 * Read-only utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchAnalyzer
{
    /**
     * @var string[][]
     */
    private $publicPropertyNamesForType = [];

    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(SmartClassReflector $smartClassReflector, NodeTypeResolver $nodeTypeResolver)
    {
        $this->smartClassReflector = $smartClassReflector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isTypeAndProperty(Node $node, string $type, string $property): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var Identifier $propertyFetchName */
        $propertyFetchName = $node->name;

        $nodePropertyName = $propertyFetchName->toString();

        return $nodePropertyName === $property;
    }

    /**
     * @param string[] $types
     */
    public function isTypesAndProperty(Node $node, array $types, string $property): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);
        if (! array_intersect($types, $varNodeTypes)) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        $nodePropertyName = $identifierNode->toString();

        return $nodePropertyName === $property;
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

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        $nodePropertyName = $identifierNode->toString();

        $publicPropertyNames = $this->getPublicPropertyNamesForType($type);

        return ! in_array($nodePropertyName, $publicPropertyNames, true);
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

        /** @var Identifier $propertyFetchName */
        $propertyFetchName = $node->name;

        return in_array($propertyFetchName->toString(), $propertyNames, true);
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

    /**
     * @return string[]
     */
    private function getPublicPropertyNamesForType(string $type): array
    {
        if (isset($this->publicPropertyNamesForType[$type])) {
            return $this->publicPropertyNamesForType[$type];
        }

        $classReflection = $this->smartClassReflector->reflect($type);
        if ($classReflection === null) {
            return [];
        }

        $publicProperties = $classReflection->getProperties(ReflectionProperty::IS_PUBLIC);

        return $this->publicPropertyNamesForType[$type] = array_keys($publicProperties);
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
