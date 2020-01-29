<?php

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty;


class foo
{
    public function bar()
    {
        new class
        {
            public function baz()
            {
            }
        };
        moreCode();
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
                5: RandomProperty
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
                        className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
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
                                className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                methodName: bar
                            )
                        )
                        stmts: array(
                            0: Stmt_Expression #6(
                                expr: Expr_New #7(
                                    class: Stmt_Class #8(
                                        flags: 0
                                        stmts: array(
                                            0: Stmt_ClassMethod #9(
                                                flags: MODIFIER_PUBLIC (1)
                                                byRef: false
                                                name: Identifier #10(
                                                    name: baz
                                                    attributes: array(
                                                        PhpParser\Node\Stmt\ClassLike: Stmt_Class #8
                                                        className: null
                                                        methodName: baz
                                                    )
                                                )
                                                attributes: array(
                                                    PhpParser\Node\Stmt\ClassLike: Stmt_Class #8
                                                    className: null
                                                    methodName: baz
                                                )
                                            )
                                        )
                                        attributes: array(
                                            PhpParser\Node\Stmt\ClassLike: Stmt_Class #8
                                            className: null
                                            methodName: bar
                                        )
                                    )
                                    attributes: array(
                                        PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                        className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                        methodName: bar
                                    )
                                )
                                attributes: array(
                                    PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                    className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                    methodName: bar
                                )
                            )
                            1: Stmt_Expression #11(
                                expr: Expr_FuncCall #12(
                                    name: Name #13(
                                        parts: array(
                                            0: moreCode
                                        )
                                        attributes: array(
                                            PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                            className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                            methodName: baz
                                        )
                                    )
                                    attributes: array(
                                        PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                        className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                        methodName: baz
                                    )
                                )
                                attributes: array(
                                    PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                                    className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                                    methodName: baz
                                )
                            )
                        )
                        attributes: array(
                            PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                            className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
                            methodName: bar
                        )
                    )
                )
                attributes: array(
                    PhpParser\Node\Stmt\ClassLike: Stmt_Class #2
                    className: Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\RandomProperty\foo
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