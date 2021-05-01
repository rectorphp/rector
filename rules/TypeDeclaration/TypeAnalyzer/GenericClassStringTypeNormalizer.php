<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

final class GenericClassStringTypeNormalizer
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function normalize(Type $type): Type
    {
        return TypeTraverser::map($type, function (Type $type, $callback): Type {
            if (! $type instanceof ConstantStringType) {
                return $callback($type);
            }

            // skip string that look like classe
<<<<<<< HEAD
            if ($type->getValue() === 'error') {
=======
            if (in_array($type->getValue(), ['error'], true)) {
>>>>>>> ca2d830a6 (exclude error string from class names)
                return $callback($type);
            }

            if (! $this->reflectionProvider->hasClass($type->getValue())) {
                return $callback($type);
            }

            return new GenericClassStringType(new ObjectType($type->getValue()));
        });
    }
}
