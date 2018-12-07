<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Inheritance;

class A {
    /** @return A */
    public function test() {
        return $this;
    }
}

class B extends A {
    public function test() {
        return $this;
    }
}

class C extends B {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "A" instead, which is less accurate but valid.
     *
     * @return C
     */
    public function test() {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Inheritance;

class A {
    /** @return A */
    public function test(): A {
        return $this;
    }
}

class B extends A {
    public function test(): A {
        return $this;
    }
}

class C extends B {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "A" instead, which is less accurate but valid.
     *
     * @return C
     */
    public function test(): A {
        return $this;
    }
}

?>
