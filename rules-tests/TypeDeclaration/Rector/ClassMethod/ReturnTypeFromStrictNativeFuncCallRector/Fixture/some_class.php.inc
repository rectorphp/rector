<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeFuncCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return strlen('value');
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeFuncCallRector\Fixture;

final class SomeClass
{
    public function run(): int
    {
        return strlen('value');
    }
}

?>
