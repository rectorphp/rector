<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

/**
 * @template T
 */
abstract class SkipClassStringGenericType
{
    /**
     * @return class-string<T>
     */
    abstract protected function getClassStringForT(): string;
}
