<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance;

class Foo
{}

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance;

class Foo
{}

class A {
    /** @param Foo $a */
    public function test2(\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance\Foo $a) {}
}

class B extends A {
    /** @param null|Foo $a */
    public function test2(?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\NullableInheritance\Foo $a) {}
}
?>
