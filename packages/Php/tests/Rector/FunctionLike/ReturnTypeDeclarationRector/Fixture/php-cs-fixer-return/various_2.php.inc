<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various3;

/** @return void */ function my_foo() {
}
/** @return iterable */ function my_foo2($value) {
    return $value;
}
/** @return object */ function my_foo3($value) {
    return $value;
}
/** @return int */ function my_foo4($value) {
    return $value;
}
/** @return array */ function my_foo5($value) {
    return $value;
}
/** @return callable */ function my_foo6($value) {
    return $value;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various3;

/** @return void */ function my_foo(): void {
}
/** @return iterable */ function my_foo2($value): iterable {
    return $value;
}
/** @return object */ function my_foo3($value): object {
    return $value;
}
/** @return int */ function my_foo4($value): int {
    return $value;
}
/** @return array */ function my_foo5($value): array {
    return $value;
}
/** @return callable */ function my_foo6($value): callable {
    return $value;
}

?>
