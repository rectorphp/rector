<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\NodeFactory;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class ClassConstFetchFactory
{
    /**
     * @param ObjectType|UnionType $type
     * @return ClassConstFetch[]
     */
    public function createFromType(Type $type): array
    {
        $classConstTypes = [];
        if ($type instanceof ShortenedObjectType) {
            $classConstTypes[] = new ClassConstFetch(new FullyQualified($type->getFullyQualifiedName()), 'class');
        } elseif ($type instanceof ObjectType) {
            $classConstTypes[] = new ClassConstFetch(new FullyQualified($type->getClassName()), 'class');
        }

        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $unionedType instanceof TypeWithClassName) {
                    throw new ShouldNotHappenException();
                }

                $classConstTypes[] = new ClassConstFetch(new FullyQualified($unionedType->getClassName()), 'class');
            }
        }

        return $classConstTypes;
    }
}
