<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance;

class A {
    public function test(): self {
        return $this;
    }
}

class B extends A {
    public function test(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance\A {
        return $this;
    }
}

?>
