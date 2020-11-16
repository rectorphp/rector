<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;

$classMethod = new ClassMethod('methodName');
$classMethod->flags = Class_::MODIFIER_PUBLIC;

$variable = new Variable('some');
$number = new LNumber(10000);
$assign = new Assign($variable, $number);

$classMethod->stmts[] = new Expression($assign);

return $classMethod;
