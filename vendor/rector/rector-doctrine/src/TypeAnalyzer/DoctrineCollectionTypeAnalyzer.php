<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
/**
 * @todo replaces core one
 * @see \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
 */
final class DoctrineCollectionTypeAnalyzer
{
    public function detect(Type $type) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        $arrayType = null;
        $hasDoctrineCollectionType = \false;
        foreach ($type->getTypes() as $unionedType) {
            if ($this->isCollectionObjectType($unionedType)) {
                $hasDoctrineCollectionType = \true;
            }
            if ($unionedType instanceof ArrayType) {
                $arrayType = $unionedType;
            }
        }
        if (!$hasDoctrineCollectionType) {
            return \false;
        }
        return $arrayType !== null;
    }
    private function isCollectionObjectType(Type $type) : bool
    {
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        if ($type instanceof ShortenedObjectType) {
            $className = $type->getFullyQualifiedName();
        } else {
            $className = $type->getClassName();
        }
        return $className === 'Doctrine\\Common\\Collections\\Collection';
    }
}
