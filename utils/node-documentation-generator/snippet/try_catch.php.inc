<?php

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;

$variable = new Variable('exceptionVariableName');
$catch = new Catch_([new FullyQualified('CatchedType')], $variable);
$stmts = [new FuncCall(new Name('funcCallName'))];

return new TryCatch($stmts, [$catch]);
