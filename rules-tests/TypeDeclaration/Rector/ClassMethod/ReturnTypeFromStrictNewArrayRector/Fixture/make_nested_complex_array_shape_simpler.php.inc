<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class MakeNestedComplexArrayShapeSimpler
{
    public function run(int $age)
    {
        $items = [];

        $item = [];
        $item['one'] = 1;
        $item['two'] = 1;
        $item['three'] = $age;
        $item['four'] = 1;
        $item['five'] = 1;

        $items[] = $item;

        return $items;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class MakeNestedComplexArrayShapeSimpler
{
    /**
     * @return mixed[]
     */
    public function run(int $age): array
    {
        $items = [];

        $item = [];
        $item['one'] = 1;
        $item['two'] = 1;
        $item['three'] = $age;
        $item['four'] = 1;
        $item['five'] = 1;

        $items[] = $item;

        return $items;
    }
}

?>
