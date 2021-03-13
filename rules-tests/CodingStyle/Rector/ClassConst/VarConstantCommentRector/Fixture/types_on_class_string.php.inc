<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class TypesOnClassString
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

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class TypesOnClassString
{
    /**
     * @var array<class-string<\PhpParser\Node\Expr>>
     */
    private const MODIFYING_NODES = [
        'PhpParser\Node\Expr\AssignOp',
        'PhpParser\Node\Scalar\String_',
    ];
}

?>
