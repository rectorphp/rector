<?php

declare(strict_types=1);

namespace Rector\Legacy\NodeFactory;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;

final class ClassMethodFactory
{
    public function createClassMethodFromFunction(string $methodName, Function_ $function): ClassMethod
    {
        $methodBuilder = new MethodBuilder($methodName);
        $methodBuilder->makePublic();
        $methodBuilder->makeStatic();
        $methodBuilder->addStmts($function->stmts);

        return $methodBuilder->getNode();
    }
}
