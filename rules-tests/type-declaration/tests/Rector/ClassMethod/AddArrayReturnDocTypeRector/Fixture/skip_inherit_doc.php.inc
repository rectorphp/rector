<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

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


abstract class ParentClassWithDefinedReturnSecond
{
    /**
     * @return mixed[]
     */
    public function getData()
    {
        return ['...'];
    }
}
