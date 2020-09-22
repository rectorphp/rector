<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

$classMethod = new ClassMethod('methodName');
$classMethod->flags = Class_::MODIFIER_PRIVATE;

$param = new Param(new Variable('paramName'));
$classMethod->params = [$param];
$classMethod->returnType = new Identifier('string');

return $classMethod;
