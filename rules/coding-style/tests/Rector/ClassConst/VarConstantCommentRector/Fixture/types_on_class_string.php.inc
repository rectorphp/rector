<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class TypeOnClassString
{
    /**
     * @var string[]
     */
    private const MODIFYING_NODES = [
        'PhpParser\Node\Expr\AssignOp',
        'PhpParser\Node\Scalar\String_',
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class TypeOnClassString
{
    /**
     * @var class-string[]
     */
    private const MODIFYING_NODES = [
        'PhpParser\Node\Expr\AssignOp',
        'PhpParser\Node\Scalar\String_',
    ];
}

?>
