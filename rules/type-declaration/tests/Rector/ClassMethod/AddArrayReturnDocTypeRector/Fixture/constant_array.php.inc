<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ConstantArray
{
    /**
     * @return mixed[]
     */
    public function someMethod(): array
    {
        return [
            42,
            [42],
        ];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ConstantArray
{
    /**
     * @return int[]|int[][]
     */
    public function someMethod(): array
    {
        return [
            42,
            [42],
        ];
    }
}

?>
