<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $values = [];

        return $values;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class SomeClass
{
    public function run(): array
    {
        $values = [];

        return $values;
    }
}

?>
