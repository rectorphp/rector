<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class A {
    /** @return iterable */
    public function getIterable() {}
}
class B extends A {
    /** @return array */
    public function getIterable() {}
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class A {
    /** @return iterable */
    public function getIterable(): iterable {}
}
class B extends A {
    /** @return array */
    public function getIterable(): array {}
}

?>
