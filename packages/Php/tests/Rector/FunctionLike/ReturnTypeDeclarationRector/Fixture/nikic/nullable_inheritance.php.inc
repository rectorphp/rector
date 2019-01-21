<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test() {}
}

class B extends A {
    /** @return Foo */
    public function test() {}
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test(): ?Foo {}
}

class B extends A {
    /** @return Foo */
    public function test(): Foo {}
}
?>
