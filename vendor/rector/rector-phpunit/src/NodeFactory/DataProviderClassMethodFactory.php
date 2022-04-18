<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
final class DataProviderClassMethodFactory
{
    public function createFromRecipe(\Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe $dataProviderClassMethodRecipe) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($dataProviderClassMethodRecipe->getMethodName());
        $methodBuilder->makePublic();
        $classMethod = $methodBuilder->getNode();
        foreach ($dataProviderClassMethodRecipe->getArgs() as $arg) {
            $value = $arg->value;
            if (!$value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($value->items as $arrayItem) {
                if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $returnStatement = new \PhpParser\Node\Expr\Yield_(new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($arrayItem->value)]));
                $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($returnStatement);
            }
        }
        $this->decorateClassMethodWithReturnTypeAndTag($classMethod);
        return $classMethod;
    }
    private function decorateClassMethodWithReturnTypeAndTag(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $classMethod->returnType = new \PhpParser\Node\Name\FullyQualified('Iterator');
    }
}
