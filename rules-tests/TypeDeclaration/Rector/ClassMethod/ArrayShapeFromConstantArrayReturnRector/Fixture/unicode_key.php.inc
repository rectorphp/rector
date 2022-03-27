<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class UnicodeKey
{
    public function run(string $name)
    {
        return ['äÄ' => $name];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class UnicodeKey
{
    /**
     * @return array{äÄ: string}
     */
    public function run(string $name)
    {
        return ['äÄ' => $name];
    }
}

?>
