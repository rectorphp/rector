<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class AnIterableClass {
    /** @return iterable */
    public function getIterable($value) {
        return $value;
    }
}
class BuildOnThePreviousClass extends AnIterableClass {
    /** @return array */
    public function getIterable($value) {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Iterable;

class AnIterableClass {
    public function getIterable($value): iterable {
        return $value;
    }
}
class BuildOnThePreviousClass extends AnIterableClass {
    /** @return array */
    public function getIterable($value): iterable {
        return $value;
    }
}

?>
