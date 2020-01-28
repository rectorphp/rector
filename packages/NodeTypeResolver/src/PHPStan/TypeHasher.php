<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class TypeHasher
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    public function __construct(PHPStanStaticTypeMapper $phpStanStaticTypeMapper)
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function createTypeHash(Type $type): string
    {
        if ($type instanceof MixedType) {
            return serialize($type);
        }

        if ($type instanceof ArrayType) {
            // @todo sort to make different order identical
            return $this->createTypeHash($type->getItemType()) . '[]';
        }

        if ($type instanceof ShortenedObjectType) {
            return $type->getFullyQualifiedName();
        }

        if ($type instanceof TypeWithClassName) {
            return $type->getClassName();
        }

        if ($type instanceof ConstantType) {
            if (method_exists($type, 'getValue')) {
                return get_class($type) . $type->getValue();
            }

            throw new ShouldNotHappenException();
        }

        if ($type instanceof UnionType) {
            $types = $type->getTypes();
            sort($types);
            $type = new UnionType($types);
        }

        return $this->phpStanStaticTypeMapper->mapToDocString($type);
    }
}
