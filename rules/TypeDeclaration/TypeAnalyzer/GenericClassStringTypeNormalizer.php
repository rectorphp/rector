<?php

declare (strict_types=1);
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
use RectorPrefix20210723\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class GenericClassStringTypeNormalizer
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20210723\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->parameterProvider = $parameterProvider;
    }
    public function normalize(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        $isAutoImport = $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES);
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, $callback) use($isAutoImport) : Type {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                $callbackType = $callback($type);
                if ($callbackType instanceof \PHPStan\Type\ArrayType) {
                    return $callbackType;
                }
                $typeWithFullyQualifiedObjectType = $this->verifyAutoImportedFullyQualifiedType($type, $isAutoImport);
                if ($typeWithFullyQualifiedObjectType instanceof \PHPStan\Type\Type) {
                    return $typeWithFullyQualifiedObjectType;
                }
                return $callbackType;
            }
            // skip string that look like classe
            if ($type->getValue() === 'error') {
                return $callback($type);
            }
            if (!$this->reflectionProvider->hasClass($type->getValue())) {
                return $callback($type);
            }
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($type->getValue()));
        });
    }
    private function verifyAutoImportedFullyQualifiedType(\PHPStan\Type\Type $type, bool $isAutoImport) : ?\PHPStan\Type\Type
    {
        if ($type instanceof \PHPStan\Type\UnionType) {
            $unionTypes = $type->getTypes();
            $types = [];
            $hasFullyQualifiedObjectType = \false;
            foreach ($unionTypes as $unionType) {
                if ($this->isAutoImportFullyQualifiedObjectType($unionType, $isAutoImport)) {
                    /** @var FullyQualifiedObjectType $unionType */
                    $types[] = new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($unionType->getClassName()));
                    $hasFullyQualifiedObjectType = \true;
                    continue;
                }
                $types[] = $unionType;
            }
            if ($hasFullyQualifiedObjectType) {
                return new \PHPStan\Type\UnionType($types);
            }
            return $type;
        }
        if ($this->isAutoImportFullyQualifiedObjectType($type, $isAutoImport)) {
            /** @var FullyQualifiedObjectType $type */
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($type->getClassName()));
        }
        return null;
    }
    private function isAutoImportFullyQualifiedObjectType(\PHPStan\Type\Type $type, bool $isAutoImport) : bool
    {
        return $isAutoImport && $type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && \strpos($type->getClassName(), '\\') === \false;
    }
}
