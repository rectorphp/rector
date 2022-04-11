<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipKeyWithSpaceDotInnerArray
{
    public function getDefaultContext(): array
    {
        return [
             'foo' => [
                 'string with.dot' => '',
             ],
        ];
    }
}
