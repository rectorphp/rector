<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @param Foo $a */
    public function test2($a) {}
}

class B extends A {
    /** @param null|Foo $a */
    public function test2($a) {}
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @param Foo $a */
    public function test2(Foo $a) {}
}

class B extends A {
    /** @param null|Foo $a */
    public function test2(?Foo $a) {}
}
?>
