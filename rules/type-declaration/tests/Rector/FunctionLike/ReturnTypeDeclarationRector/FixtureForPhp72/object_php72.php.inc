<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ObjectPhp72;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ObjectPhp72;

class A {
    public function getObject($value): object {
        return $value;
    }
}
class B extends A {
    /** @return Foo */
    public function getObject($value): object {
        return $value;
    }
}
?>
