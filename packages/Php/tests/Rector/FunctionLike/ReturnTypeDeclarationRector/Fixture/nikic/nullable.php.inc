<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test($value) {
    return $value;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test($value): ?Foo {
    return $value;
}

?>
