<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

<<<<<<< HEAD
final class TypesOnClassString
=======
final class TypeOnClassString
>>>>>>> c5fc8c43b... add string vs class-string
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

<<<<<<< HEAD
final class TypesOnClassString
{
    /**
     * @var array<class-string<\PhpParser\Node\Expr>>
=======
final class TypeOnClassString
{
    /**
     * @var class-string[]
>>>>>>> c5fc8c43b... add string vs class-string
     */
    private const MODIFYING_NODES = [
        'PhpParser\Node\Expr\AssignOp',
        'PhpParser\Node\Scalar\String_',
    ];
}

?>
