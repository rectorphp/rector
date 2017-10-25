<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
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
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $variableNodeType = $node->var->getAttribute(Attribute::TYPES);

        if ($variableNodeType !== $type) {
            return false;
        }

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

        $variableNodeType = $node->var->getAttribute(Attribute::TYPES);

        if (! in_array($variableNodeType, $types, true)) {
            return false;
        }

        $nodePropertyName = $node->name->toString();

        return $nodePropertyName === $property;
    }

    public function isMagicOnType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $variableNodeType = $node->var->getAttribute(Attribute::TYPES);
        if ($variableNodeType !== $type) {
            return false;
        }

        $nodePropertyName = $node->name->name;
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

        $nodePropertyName = $node->name->toString();

        return in_array($nodePropertyName, $properties, true);
    }

    /**
     * @param string[] $types
     */
    public function isTypes(Node $node, array $types): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $variableNodeType = $node->var->getAttribute(Attribute::TYPES);

        return in_array($variableNodeType, $types, true);
    }

    /**
     * @param string[] $types
     */
    public function matchTypes(Node $node, array $types): ?string
    {
        if (! $this->isTypes($node, $types)) {
            return null;
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
        $publicProperties = $classReflection->getProperties(ReflectionProperty::IS_PUBLIC);

        return $this->publicPropertyNamesForType[$type] = array_keys($publicProperties);
    }
}
