<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SomeClass
{
    public function run(string $name)
    {
        return ['name' => $name];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SomeClass
{
    /**
     * @return array{name: string}
     */
    public function run(string $name)
    {
        return ['name' => $name];
    }
}

?>
