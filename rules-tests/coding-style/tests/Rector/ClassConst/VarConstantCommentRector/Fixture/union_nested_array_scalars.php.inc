<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class UnionNestedArrayScalars
{
    public const STRING_ONLY = [
        'key' => 'value',
    ];

    public const NESTED_STRING_INT = [
        'key2' => ['value2', 1234],
    ];

    public const STRING_AND_NULL = [
        'key' => 'value',
        1 => null,
    ];
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class UnionNestedArrayScalars
{
    /**
     * @var array<string, string>
     */
    public const STRING_ONLY = [
        'key' => 'value',
    ];

    /**
     * @var array<string, array<int|string>>
     */
    public const NESTED_STRING_INT = [
        'key2' => ['value2', 1234],
    ];

    /**
     * @var array<int|string, string|null>
     */
    public const STRING_AND_NULL = [
        'key' => 'value',
        1 => null,
    ];
}

?>
