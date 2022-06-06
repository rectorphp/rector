<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\Enum\ObjectReference;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator;
use RectorPrefix20220606\Rector\PHPUnit\NodeManipulator\StmtManipulator;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
final class SetUpClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator
     */
    private $setUpMethodDecorator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeManipulator\StmtManipulator
     */
    private $stmtManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(SetUpMethodDecorator $setUpMethodDecorator, StmtManipulator $stmtManipulator, NodeFactory $nodeFactory)
    {
        $this->setUpMethodDecorator = $setUpMethodDecorator;
        $this->stmtManipulator = $stmtManipulator;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function createSetUpMethod(array $stmts) : ClassMethod
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);
        $classMethodBuilder = new MethodBuilder(MethodName::SET_UP);
        $classMethodBuilder->makeProtected();
        $classMethodBuilder->addStmt($this->createParentStaticCall());
        $classMethodBuilder->addStmts($stmts);
        $classMethod = $classMethodBuilder->getNode();
        $this->setUpMethodDecorator->decorate($classMethod);
        return $classMethod;
    }
    public function createParentStaticCall() : Expression
    {
        $parentSetupStaticCall = $this->nodeFactory->createStaticCall(ObjectReference::PARENT, MethodName::SET_UP);
        return new Expression($parentSetupStaticCall);
    }
}
