<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ObjectPhp72;

class A {
    /** @return object */
    public function getObject() {}
}
class B extends A {
    /** @return Foo */
    public function getObject() {}
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ObjectPhp72;

class A {
    /** @return object */
    public function getObject(): object {}
}
class B extends A {
    /** @return Foo */
    public function getObject(): Foo {}
}
?>
