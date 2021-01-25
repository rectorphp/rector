<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Inheritance;

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

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Inheritance;

class A {
    public function test(): self {
        return $this;
    }
}

class B extends A {
    public function test(): \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Inheritance\A {
        return $this;
    }
}

?>
