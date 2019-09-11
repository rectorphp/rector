<?php declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;

final class DataProviderClassMethodFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(BuilderFactory $builderFactory, DocBlockManipulator $docBlockManipulator)
    {
        $this->builderFactory = $builderFactory;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function createFromRecipe(DataProviderClassMethodRecipe $dataProviderClassMethodRecipe): ClassMethod
    {
        $methodBuilder = $this->builderFactory->method($dataProviderClassMethodRecipe->getMethodName());
        $methodBuilder->makePublic();

        $classMethod = $methodBuilder->getNode();

        foreach ($dataProviderClassMethodRecipe->getArgs() as $arg) {
            $value = $arg->value;
            if ($value instanceof Array_) {
                foreach ($value->items as $arrayItem) {
                    $returnStatement = new Yield_($arrayItem->value);
                    $classMethod->stmts[] = new Expression($returnStatement);
                }
            }
        }

        $this->decorateClassMethodWithReturnTypeAndTag($classMethod, $dataProviderClassMethodRecipe);

        return $classMethod;
    }

    private function decorateClassMethodWithReturnTypeAndTag(
        ClassMethod $classMethod,
        DataProviderClassMethodRecipe $dataProviderClassMethodRecipe
    ): void {
        $classMethod->returnType = new Identifier('iterable');

        $type = $dataProviderClassMethodRecipe->getType();
        if ($type === null) {
            return;
        }

        $this->docBlockManipulator->addReturnTag($classMethod, $type);
    }
}
