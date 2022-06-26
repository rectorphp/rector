<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class ReturnOverrideWithArray
{
    public function run()
    {
        $values = [];

        $values = [1000];

        return $values;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class ReturnOverrideWithArray
{
    public function run(): array
    {
        $values = [];

        $values = [1000];

        return $values;
    }
}

?>
