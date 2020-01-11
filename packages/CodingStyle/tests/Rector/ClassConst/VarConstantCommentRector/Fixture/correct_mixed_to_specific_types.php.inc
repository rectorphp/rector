<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class CorrectMixedToSpecificTypes
{
    /**
     * @var mixed[]
     */
    public const ARRAY_CONST = [
        'key' => 'value',
        'key2' => ['value2', 1234],
        1 => null,
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class CorrectMixedToSpecificTypes
{
    /**
     * @var array<int|string, string|array<int|string>|null>
     */
    public const ARRAY_CONST = [
        'key' => 'value',
        'key2' => ['value2', 1234],
        1 => null,
    ];
}

?>
