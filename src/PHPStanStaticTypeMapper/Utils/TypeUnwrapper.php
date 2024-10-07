<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\CallableType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
final class TypeUnwrapper
{
    public function unwrapFirstObjectTypeFromUnionType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function unwrapFirstCallableTypeFromUnionType(Type $type) : ?Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof CallableType) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function isIterableTypeValue(string $className, Type $type) : bool
    {
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        // get the namespace from $className
        $classNamespace = $this->namespace($className);
        // get the namespace from $parameterReflection
        $reflectionNamespace = $this->namespace($type->getClassName());
        // then match with
        return $reflectionNamespace === $classNamespace && \substr_compare($type->getClassName(), '\\TValue', -\strlen('\\TValue')) === 0;
    }
    public function isIterableTypeKey(string $className, Type $type) : bool
    {
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        // get the namespace from $className
        $classNamespace = $this->namespace($className);
        // get the namespace from $parameterReflection
        $reflectionNamespace = $this->namespace($type->getClassName());
        // then match with
        return $reflectionNamespace === $classNamespace && \substr_compare($type->getClassName(), '\\TKey', -\strlen('\\TKey')) === 0;
    }
    public function removeNullTypeFromUnionType(UnionType $unionType) : Type
    {
        return TypeCombinator::removeNull($unionType);
    }
    private function namespace(string $class) : string
    {
        return \implode('\\', \array_slice(\explode('\\', $class), 0, -1));
    }
}
