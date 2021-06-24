<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

final class NestedGetData
{
    public function getData()
    {
        return [
            'key',
            'value'
        ];
    }
}
