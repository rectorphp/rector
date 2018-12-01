<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Object;

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

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Object;

class A {
    /** @return object */
    public function getObject() {}
}
class B extends A {
    /** @return Foo */
    public function getObject(): Foo {}
}
?>
