<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

class UnionNestedAndSingleLevelArrayScalars
{
    /**
     * @var mixed[]
     */
    public const ARRAY_CONST = [
        'key2' => ['value2', 1234],
        1 => null,
    ];
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

class UnionNestedAndSingleLevelArrayScalars
{
    /**
     * @var array<int|string, int[]|string[]|null>
     */
    public const ARRAY_CONST = [
        'key2' => ['value2', 1234],
        1 => null,
    ];
}

?>
