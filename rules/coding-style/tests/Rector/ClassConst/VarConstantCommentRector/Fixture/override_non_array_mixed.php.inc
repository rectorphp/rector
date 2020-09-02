<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class OverrideNonArrayMixed
{
    /**
     * @var mixed
     */
    public const NESTED_STRINGS = [
        'get_it' => 'yes'
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class OverrideNonArrayMixed
{
    /**
     * @var array<string, string>
     */
    public const NESTED_STRINGS = [
        'get_it' => 'yes'
    ];
}

?>
