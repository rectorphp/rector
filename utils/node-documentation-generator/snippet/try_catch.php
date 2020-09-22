<?php

declare(strict_types=1);

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\TryCatch;

$variable = new Variable('exceptionVariableName');
$catch = new Catch_([new FullyQualified('CatchedType')], $variable);

$funcCall = new FuncCall(new Name('funcCallName'));
$stmts = [new Expression($funcCall)];

return new TryCatch($stmts, [$catch]);
