<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Object;

class A {
    /** @return object */
    public function getObject($value) {
        return $value;
    }
}
class B extends A {
    /** @return Foo */
    public function getObject($value) {
        return $value;
    }
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Object;

class A {
    /** @return object */
    public function getObject($value): object {
        return $value;
    }
}
class B extends A {
    /** @return Foo */
    public function getObject($value): Foo {
        return $value;
    }
}
?>
