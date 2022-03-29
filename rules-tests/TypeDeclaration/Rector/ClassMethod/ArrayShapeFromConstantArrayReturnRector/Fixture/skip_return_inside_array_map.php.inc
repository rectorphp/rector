<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipReturnInsideArrayMap
{
    public function noReturn(): void
    {
        array_map(
                    static function ($value): array {
                        return [
                            'a' => 1,
                            'b' => 2,
                        ];
                    },
                    [],
        );
    }
}