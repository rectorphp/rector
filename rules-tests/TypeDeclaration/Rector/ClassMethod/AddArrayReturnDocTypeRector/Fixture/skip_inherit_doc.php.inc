<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ParentClassWithDefinedReturnSecond;

class SkipInheritDoc extends ParentClassWithDefinedReturnSecond
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            [
                'a' => 'string',
                'b' => 1,
                'c' => 1.0
            ]
        ];
    }
}
