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

    public function isMagicPropertyFetchOnType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $variableNodeType = $node->var->getAttribute(Attribute::TYPE);
        if ($variableNodeType !== $type) {
            return false;
        }

        $nodePropertyName = $node->name->name;
        $publicPropertyNames = $this->getPublicPropertyNamesForType($type);

        return ! in_array($nodePropertyName, $publicPropertyNames, true);
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
