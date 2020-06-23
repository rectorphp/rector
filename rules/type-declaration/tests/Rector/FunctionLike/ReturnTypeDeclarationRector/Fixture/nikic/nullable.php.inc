<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

class Foo {}

/** @return Foo|null */
function test($value) {
    return $value;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

class Foo {}

function test($value): ?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable\Foo {
    return $value;
}

?>
