<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class IncludeConstants
{
    public const NAME = 'name';

    public function run(string $name)
    {
        return [self::NAME => $name];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class IncludeConstants
{
    public const NAME = 'name';

    /**
     * @return array{name: string}
     */
    public function run(string $name)
    {
        return [self::NAME => $name];
    }
}

?>
