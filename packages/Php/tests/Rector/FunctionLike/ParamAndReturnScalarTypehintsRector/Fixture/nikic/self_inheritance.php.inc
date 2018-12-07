<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\SelfInheritance;

class P {}

class A extends P {
    /** @return self */
    public function foo() {}

    /** @return parent */
    public function bar() {}
}

class B extends A {
    public function foo() {}

    public function bar() {}
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\SelfInheritance;

class P {}

class A extends P {
    /** @return self */
    public function foo(): self {}

    /** @return parent */
    public function bar(): parent {}
}

class B extends A {
    public function foo(): \Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\SelfInheritance\A {}

    public function bar(): \Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\SelfInheritance\P {}
}

?>
