<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use Roave\BetterReflection\Reflection\ReflectionProperty;

final class PropertyReflector
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var mixed[][]
     */
    private $cachedClassPropertyTypes = [];

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function reflectClassProperty(string $class, string $method): ?ReflectionProperty
    {
        $classReflection = $this->smartClassReflector->reflect($class);
        if ($classReflection === null) {
            return null;
        }

        return $classReflection->getImmediateProperties()[$method] ?? null;
    }

    public function getPropertyType(string $class, string $property): ?string
    {
        if (isset($this->cachedClassPropertyTypes[$class][$property])) {
            return $this->cachedClassPropertyTypes[$class][$property];
        }

        $propertyReflection = $this->reflectClassProperty($class, $property);

        $type = null;
        if ($propertyReflection) {
            $type = $this->resolveTypeFromReflectionProperty($propertyReflection);
        }

        return $this->cachedClassPropertyTypes[$class][$property] = $type;
    }

    private function resolveTypeFromReflectionProperty(ReflectionProperty $reflectionProperty): ?string
    {
        $types = $reflectionProperty->getDocBlockTypes();
        if (! isset($types[0])) {
            return null;
        }

        if ($types[0] instanceof Array_) {
            $valueType = $types[0]->getValueType();
            if ($valueType instanceof Object_) {
                return ltrim((string) $valueType->getFqsen(), '\\');
            }
        }

        if ($types[0] instanceof Object_) {
            return ltrim((string) $types[0]->getFqsen(), '\\');
        }

        return null;
    }
}
