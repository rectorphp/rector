<?php

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Return_;

$name = new Name('true');
$constFetch = new ConstFetch($name);
$stmt = new Return_();

return new ElseIf_($constFetch, [$stmt]);
