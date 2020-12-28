<?php


namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

abstract class A {
	/** @return array{a: string, b: int, c: float}[] */
	public function X(): array { return []; }
}

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class B extends A
{
	public function X(): array { return [ [ 'a' => 'string', 'b' => 1, 'c' => 1.0 ] ]; }
}
?>
