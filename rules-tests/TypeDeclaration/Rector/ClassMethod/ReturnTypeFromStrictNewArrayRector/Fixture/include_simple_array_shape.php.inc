<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class IncludeSimpleArrayShape
{
    public function run()
    {
        $items = [];

        $items['one'] = 1;
        $items['two'] = 1;
        $items['three'] = 1;

        return $items;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class IncludeSimpleArrayShape
{
    /**
     * @return array{one: int, two: int, three: int}
     */
    public function run(): array
    {
        $items = [];

        $items['one'] = 1;
        $items['two'] = 1;
        $items['three'] = 1;

        return $items;
    }
}

?>
