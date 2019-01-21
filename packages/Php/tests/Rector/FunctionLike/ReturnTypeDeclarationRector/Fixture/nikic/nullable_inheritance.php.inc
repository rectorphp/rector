<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test($value) {
        return $value;
    }
}

class B extends A {
    /** @return Foo */
    public function test($value) {
        return $value;
    }
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\NullableInheritance;

class A {
    /** @return null|Foo */
    public function test($value): ?Foo {
        return $value;
    }
}

class B extends A {
    /** @return Foo */
    public function test($value): Foo {
        return $value;
    }
}
?>
