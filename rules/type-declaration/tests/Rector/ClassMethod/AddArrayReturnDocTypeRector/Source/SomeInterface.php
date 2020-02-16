<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

interface SomeInterface
{
    /**
     * @return string[]
     */
    public function someMethod(): array;
}
