<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Scalar\String_;

final class ImproveArrayTypes
{
    /**
     * @var string[]
     */
    private const MODIFYING_NODES = [
        AssignOp::class,
        String_::class,
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Scalar\String_;

final class ImproveArrayTypes
{
    /**
     * @var array<class-string<\PhpParser\Node>>
     */
    private const MODIFYING_NODES = [
        AssignOp::class,
        String_::class,
    ];
}

?>
