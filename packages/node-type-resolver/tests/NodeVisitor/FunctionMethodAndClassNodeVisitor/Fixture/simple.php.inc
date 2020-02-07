<?php

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Simple;


class foo
{
    public function bar()
    {
    }
}
?>
-----
array(
    0: Stmt_Namespace #0(
        name: Name #1(
            parts: array(
                0: Rector
                1: NodeTypeResolver
                2: Tests
                3: NodeVisitor
                4: FunctionMethodAndClassNodeVisitorTest
                5: Simple
            )
            attributes: array(
                PhpParser\Node\Stmt\ClassLike: null
                className: null
                methodName: null
            )
        )
        stmts: array(
            0: Stmt_Class #2(
                flags: 0
                name: Identifier #3(
                    name: foo
                    attributes: array(
                        PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                        className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Simple\foo
                        methodName: null
                    )
                )
                stmts: array(
                    0: Stmt_ClassMethod #4(
                        flags: MODIFIER_PUBLIC (1)
                        byRef: false
                        name: Identifier #5(
                            name: bar
                            attributes: array(
                                PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Simple\foo
                                methodName: bar
                            )
                        )
                        attributes: array(
                            PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                            className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Simple\foo
                            methodName: bar
                        )
                    )
                )
                attributes: array(
                    PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                    className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Simple\foo
                    methodName: null
                )
            )
        )
        attributes: array(
            PhpParser\Node\Stmt\ClassLike: null
            className: null
            methodName: null
        )
    )
)