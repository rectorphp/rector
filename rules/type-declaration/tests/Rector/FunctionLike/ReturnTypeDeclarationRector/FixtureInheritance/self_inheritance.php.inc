<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\SelfInheritance;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\SelfInheritance;

class P {}

class A extends P {
    public function foo($value): self {
        return $value;
    }

    public function bar($value): parent {
        return $value;
    }
}

class B extends A {
    public function foo($value): \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\SelfInheritance\A {
        return $value;
    }

    public function bar($value): \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\SelfInheritance\P {
        return $value;
    }
}

?>
