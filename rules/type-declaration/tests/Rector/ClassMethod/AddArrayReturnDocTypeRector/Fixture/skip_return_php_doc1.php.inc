<?php
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipReturnPhpDoc1
{
    /** @return array<int,array{a: string, b: float, c: \stdClass}> */
    public function get(): array { 
        
        return [
            [
                'a' => 'hi!',
                'b' => 3.14,
                'c' => new stdClass,
            ]
        ];
    }
}
?>
