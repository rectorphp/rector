<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;

abstract class AbstractRepositoryFindMethodCallManipulator
{
    protected function refactorToRepositoryMethod(MethodCall $methodCall, string $methodName): void
    {
        $methodCall->var = new PropertyFetch(new Variable('this'), 'repository');
        $methodCall->name = new Identifier($methodName);
    }
}
