<?php


namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

abstract class SkipArrayShapeTypeForChild
{
    /**
     * @return array{a: string, b: int, c: float}[]
     */
    public function X(): array {
        return [];
    }
}

class ChildClassWithArrayShapeReturnType extends SkipArrayShapeTypeForChild
{
    public function X(): array {
        return [
            [
                'a' => 'string',
                'b' => 1,
                'c' => 1.0
            ]
        ];
    }
}
