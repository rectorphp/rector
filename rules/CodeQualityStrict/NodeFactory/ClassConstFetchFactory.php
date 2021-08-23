<?php

declare (strict_types=1);
namespace Rector\CodeQualityStrict\NodeFactory;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class ClassConstFetchFactory
{
    /**
     * @return ClassConstFetch[]
     * @param \PHPStan\Type\ObjectType|\PHPStan\Type\UnionType $type
     */
    public function createFromType($type) : array
    {
        $classConstTypes = [];
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            $classConstTypes[] = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($type->getFullyQualifiedName()), 'class');
        } elseif ($type instanceof \PHPStan\Type\ObjectType) {
            $classConstTypes[] = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($type->getClassName()), 'class');
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $classConstTypes[] = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($unionedType->getClassName()), 'class');
            }
        }
        return $classConstTypes;
    }
}
