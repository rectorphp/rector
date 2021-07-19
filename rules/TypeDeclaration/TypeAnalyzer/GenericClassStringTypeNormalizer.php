<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\Core\Configuration\Option;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class GenericClassStringTypeNormalizer
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private ParameterProvider $parameterProvider
    ) {
    }

    public function normalize(Type $type): Type
    {
        $isAutoImport = $this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        return TypeTraverser::map($type, function (Type $type, $callback) use ($isAutoImport): Type {
            if (! $type instanceof ConstantStringType) {
                $callbackType = $callback($type);
                if ($callbackType instanceof ArrayType) {
                    return $callbackType;
                }

                $typeWithFullyQualifiedObjectType = $this->verifyAutoImportedFullyQualifiedType($type, $isAutoImport);
                if ($typeWithFullyQualifiedObjectType instanceof Type) {
                    return $typeWithFullyQualifiedObjectType;
                }

                return $callbackType;
            }

            // skip string that look like classe
            if ($type->getValue() === 'error') {
                return $callback($type);
            }

            if (! $this->reflectionProvider->hasClass($type->getValue())) {
                return $callback($type);
            }

            return new GenericClassStringType(new ObjectType($type->getValue()));
        });
    }

    private function verifyAutoImportedFullyQualifiedType(Type $type, bool $isAutoImport): ?Type
    {
        if ($type instanceof UnionType) {
            $unionTypes = $type->getTypes();
            $types = [];
            $hasFullyQualifiedObjectType = false;
            foreach ($unionTypes as $unionType) {
                if ($this->isAutoImportFullyQualifiedObjectType($unionType, $isAutoImport)) {
                    /** @var FullyQualifiedObjectType $unionType */
                    $types[] = new GenericClassStringType(new ObjectType($unionType->getClassName()));
                    $hasFullyQualifiedObjectType = true;
                    continue;
                }

                $types[] = $unionType;
            }

            if ($hasFullyQualifiedObjectType) {
                return new UnionType($types);
            }

            return $type;
        }

        if ($this->isAutoImportFullyQualifiedObjectType($type, $isAutoImport)) {
            /** @var FullyQualifiedObjectType $type */
            return new GenericClassStringType(new ObjectType($type->getClassName()));
        }

        return null;
    }

    private function isAutoImportFullyQualifiedObjectType(Type $type, bool $isAutoImport): bool
    {
        return $isAutoImport && $type instanceof FullyQualifiedObjectType && ! str_contains(
            $type->getClassName(),
            '\\'
        );
    }
}
