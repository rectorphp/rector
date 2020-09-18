<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

$classMethod = new ClassMethod('methodName');

$classMethod->flags = Class_::MODIFIER_PRIVATE;
$classMethod->params = [new Variable('param')];
$classMethod->returnType = new Identifier('string');

return $classMethod;
