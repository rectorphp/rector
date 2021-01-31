<?php
declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\PHPUnit\NodeManipulator\StmtManipulator;
use Rector\RemovingStatic\NodeFactory\SetUpFactory;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;

final class SetUpClassMethodFactory
{
    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @var StmtManipulator
     */
    private $stmtManipulator;

    /**
     * @var SetUpFactory
     */
    private $setUpFactory;

    public function __construct(
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        StmtManipulator $stmtManipulator,
        SetUpFactory $setUpFactory
    ) {
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->stmtManipulator = $stmtManipulator;
        $this->setUpFactory = $setUpFactory;
    }

    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function createSetUpMethod(array $stmts): ClassMethod
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);

        $classMethodBuilder = new MethodBuilder(MethodName::SET_UP);
        $classMethodBuilder->makeProtected();

        $classMethodBuilder->addStmt($this->setUpFactory->createParentStaticCall());
        $classMethodBuilder->addStmts($stmts);

        $classMethod = $classMethodBuilder->getNode();
        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);

        return $classMethod;
    }
}
