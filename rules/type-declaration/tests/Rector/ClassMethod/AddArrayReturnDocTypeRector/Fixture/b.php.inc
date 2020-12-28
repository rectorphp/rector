<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class B
{
	/** @return array{a: string, b: int, c: float}[] */
	public function X(): array
	{
		return [
            [
				'a' => 'string',
            	'b' => 1,
            	'c' => 1.0,
        	]
        ];
    }
}
?>
