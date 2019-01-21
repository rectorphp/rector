<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfInheritance;

class P {}

class A extends P {
    /** @return self */
    public function foo($value) {
        return $value;
    }

    /** @return parent */
    public function bar($value) {
        return $value;
    }
}

class B extends A {
    public function foo($value) {
        return $value;
    }

    public function bar($value) {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfInheritance;

class P {}

class A extends P {
    /** @return self */
    public function foo($value): self {
        return $value;
    }

    /** @return parent */
    public function bar($value): parent {
        return $value;
    }
}

class B extends A {
    public function foo($value): \Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfInheritance\A {
        return $value;
    }

    public function bar($value): \Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfInheritance\P {
        return $value;
    }
}

?>
