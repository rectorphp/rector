<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

interface SomeInterface
{
    /**
     * @return string[]
     */
    public function someMethod(): array;
}
