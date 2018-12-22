<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test() {}

    /** @param Foo $a */
    public function test2($a) {}
}

class B extends A {
    /** @return Foo */
    public function test() {}

    /** @param null|Foo $a */
    public function test2($a) {}
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test(): ?Foo {}

    /** @param Foo $a */
    public function test2(Foo $a) {}
}

class B extends A {
    /** @return Foo */
    public function test(): Foo {}

    /** @param null|Foo $a */
    public function test2(?Foo $a) {}
}
?>
