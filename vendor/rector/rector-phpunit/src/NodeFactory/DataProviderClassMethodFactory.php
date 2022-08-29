<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Builder\Method;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;
final class DataProviderClassMethodFactory
{
    public function createFromRecipe(DataProviderClassMethodRecipe $dataProviderClassMethodRecipe) : ClassMethod
    {
        $method = new Method($dataProviderClassMethodRecipe->getMethodName());
        $method->makePublic();
        $classMethod = $method->getNode();
        foreach ($dataProviderClassMethodRecipe->getArgs() as $arg) {
            $value = $arg->value;
            if (!$value instanceof Array_) {
                continue;
            }
            foreach ($value->items as $arrayItem) {
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                $returnStatement = new Yield_(new Array_([new ArrayItem($arrayItem->value)]));
                $classMethod->stmts[] = new Expression($returnStatement);
            }
        }
        $this->decorateClassMethodWithReturnTypeAndTag($classMethod);
        return $classMethod;
    }
    private function decorateClassMethodWithReturnTypeAndTag(ClassMethod $classMethod) : void
    {
        $classMethod->returnType = new FullyQualified('Iterator');
    }
}
