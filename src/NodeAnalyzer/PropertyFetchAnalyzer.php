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

    /**
     * Checks "$classOfSpecificType->anyProperty"
     */
    public function isPropertyAccessType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $nodeType = $node->var->getAttribute(Attribute::TYPE);

        return $nodeType === $type;
    }

    /**
     * Checks if accessing a real existing public property of object,
     * or some magic.
     */
    public function isPropertyAccessOfPublicProperty(PropertyFetch $propertyFetchNode, string $type): bool
    {
        $publicPropertyNames = $this->getPublicPropertyNamesForType($type);

        $nodePropertyName = $propertyFetchNode->name->name;

        return in_array($nodePropertyName, $publicPropertyNames, true);
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
