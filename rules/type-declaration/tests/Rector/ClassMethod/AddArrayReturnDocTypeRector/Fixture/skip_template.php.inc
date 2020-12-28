<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\BaseModel;

final class SkipTemplate
{
    /**
     * @template T of BaseModel
     * @param class-string<T> $otherModelClass
     * @return T[]
     */
    public static function findMany(string $otherModelClass): array
    {
        /** @var T[] $rArray */
        $rArray = []; // get data from somewhere
        return $rArray;
    }
}
