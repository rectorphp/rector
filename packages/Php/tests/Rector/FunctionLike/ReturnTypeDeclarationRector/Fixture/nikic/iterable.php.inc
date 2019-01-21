<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class A {
    /** @return iterable */
    public function getIterable($value) {
        return $value;
    }
}
class B extends A {
    /** @return array */
    public function getIterable($value) {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class A {
    /** @return iterable */
    public function getIterable($value): iterable {
        return $value;
    }
}
class B extends A {
    /** @return array */
    public function getIterable($value): array {
        return $value;
    }
}

?>
