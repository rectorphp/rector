<?php
declare(strict_types=1);

namespace Rector\RemovingStatic\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;

final class TestingClassMethodFactory
{
    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        NodeFactory $nodeFactory
    ) {
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param Assign|Expression $assignExpressionNode
     */
    public function createSetUpMethod(Node $assignExpressionNode): ClassMethod
    {
        if ($assignExpressionNode instanceof Assign) {
            $assignExpressionNode = new Expression($assignExpressionNode);
        }

        $classMethodBuilder = new MethodBuilder(MethodName::SET_UP);
        $classMethodBuilder->makeProtected();

        $parentSetupStaticCall = $this->createParentSetUpStaticCall();
        $classMethodBuilder->addStmt($parentSetupStaticCall);
        $classMethodBuilder->addStmt($assignExpressionNode);

        $classMethod = $classMethodBuilder->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);

        return $classMethod;
    }

    private function createParentSetUpStaticCall(): Expression
    {
        $parentSetupStaticCall = $this->nodeFactory->createStaticCall('parent', MethodName::SET_UP);
        return new Expression($parentSetupStaticCall);
    }
}
