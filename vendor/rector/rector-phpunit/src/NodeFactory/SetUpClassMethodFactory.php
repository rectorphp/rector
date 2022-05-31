<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator;
use Rector\PHPUnit\NodeManipulator\StmtManipulator;
use RectorPrefix20220531\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
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
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator $setUpMethodDecorator, \Rector\PHPUnit\NodeManipulator\StmtManipulator $stmtManipulator, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->setUpMethodDecorator = $setUpMethodDecorator;
        $this->stmtManipulator = $stmtManipulator;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function createSetUpMethod(array $stmts) : \PhpParser\Node\Stmt\ClassMethod
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);
        $classMethodBuilder = new \RectorPrefix20220531\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder(\Rector\Core\ValueObject\MethodName::SET_UP);
        $classMethodBuilder->makeProtected();
        $classMethodBuilder->addStmt($this->createParentStaticCall());
        $classMethodBuilder->addStmts($stmts);
        $classMethod = $classMethodBuilder->getNode();
        $this->setUpMethodDecorator->decorate($classMethod);
        return $classMethod;
    }
    public function createParentStaticCall() : \PhpParser\Node\Stmt\Expression
    {
        $parentSetupStaticCall = $this->nodeFactory->createStaticCall(\Rector\Core\Enum\ObjectReference::PARENT, \Rector\Core\ValueObject\MethodName::SET_UP);
        return new \PhpParser\Node\Stmt\Expression($parentSetupStaticCall);
    }
}
