<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class MakeComplexArrayShapeSimpler
{
    public function run()
    {
        $items = [];

        $items['one'] = 1;
        $items['two'] = 1;
        $items['three'] = 1;
        $items['four'] = 1;
        $items['five'] = 1;

        return $items;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class MakeComplexArrayShapeSimpler
{
    /**
     * @return mixed[]
     */
    public function run(): array
    {
        $items = [];

        $items['one'] = 1;
        $items['two'] = 1;
        $items['three'] = 1;
        $items['four'] = 1;
        $items['five'] = 1;

        return $items;
    }
}

?>
