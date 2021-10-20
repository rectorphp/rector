<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\PHPUnit\NodeManipulator\StmtManipulator;
use Rector\RemovingStatic\NodeFactory\SetUpFactory;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
final class SetUpClassMethodFactory
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;
    /**
     * @var \Rector\PHPUnit\NodeManipulator\StmtManipulator
     */
    private $stmtManipulator;
    /**
     * @var \Rector\RemovingStatic\NodeFactory\SetUpFactory
     */
    private $setUpFactory;
    public function __construct(\Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator, \Rector\PHPUnit\NodeManipulator\StmtManipulator $stmtManipulator, \Rector\RemovingStatic\NodeFactory\SetUpFactory $setUpFactory)
    {
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->stmtManipulator = $stmtManipulator;
        $this->setUpFactory = $setUpFactory;
    }
    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function createSetUpMethod(array $stmts) : \PhpParser\Node\Stmt\ClassMethod
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);
        $classMethodBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder(\Rector\Core\ValueObject\MethodName::SET_UP);
        $classMethodBuilder->makeProtected();
        $classMethodBuilder->addStmt($this->setUpFactory->createParentStaticCall());
        $classMethodBuilder->addStmts($stmts);
        $classMethod = $classMethodBuilder->getNode();
        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);
        return $classMethod;
    }
}
