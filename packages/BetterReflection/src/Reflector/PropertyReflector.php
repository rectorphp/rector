<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Nette\Object;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use Rector\BetterReflection\Reflection\ReflectionProperty;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;

final class PropertyReflector
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function reflectClassProperty(string $class, string $method): ?ReflectionProperty
    {
        try {
            $classReflection = $this->smartClassReflector->reflect($class);
        } catch (IdentifierNotFound $identifierNotFoundException) {
            return null;
        }

        if ($classReflection === null) {
            return null;
        }

        return $classReflection->getImmediateProperties()[$method] ?? null;
    }

    public function getPropertyType(string $class, string $property): ?string
    {
        $propertyReflection = $this->reflectClassProperty($class, $property);

        if ($propertyReflection) {
            $types = $propertyReflection->getDocBlockTypes();

            if (! isset($types[0])) {
                return null;
            }

            if ($types[0] instanceof Array_) {
                $valueType = $types[0]->getValueType();
                if ($valueType instanceof Object_) {
                    return ltrim((string) $valueType->getFqsen(), '\\');
                }
            } elseif ($types[0] instanceof Object_) {
                return ltrim((string) $types[0]->getFqsen(), '\\');
            }
        }

        return null;
    }
}
