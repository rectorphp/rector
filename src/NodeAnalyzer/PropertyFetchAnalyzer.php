<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use ReflectionProperty;

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

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function isTypeAndProperty(Node $node, string $type, string $property): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var PropertyFetch $node */
        $nodePropertyName = $node->name->toString();

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

        $variableNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        if ($variableNodeTypes === null) {
            return false;
        }

        if (! array_intersect($types, $variableNodeTypes)) {
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

        $variableNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        if (! in_array($type, $variableNodeTypes, true)) {
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

        $variableNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        if ($variableNodeTypes === null) {
            return false;
        }

        return (bool) array_intersect($variableNodeTypes, $types);
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
        return in_array($node->name->toString(), $propertyNames, true);
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

        return $node->var->getAttribute(Attribute::TYPES);
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

        $variableNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        if ($variableNodeTypes === null) {
            return false;
        }

        return in_array($type, $variableNodeTypes, true);
    }
}
