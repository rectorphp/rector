<?php
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipReturnPhpDoc2
{
    /** @return array{a: string, b: float, c: array<int, string>} */
    public function get(): array { 
        
        return [
            [
				'a' => 'hi!',
				'b' => 3.14,
				'c' => [],
			]
        ];
    }
}
?>
