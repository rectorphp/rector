<?php

declare(strict_types=1);

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\TryCatch;

$echo = new Echo_([new String_('one')]);
$tryStmts = [$echo];

$echo2 = new Echo_([new String_('two')]);
$catch = new Catch_([new FullyQualified('CatchedType')], null, [$echo2]);

$echo3 = new Echo_([new String_('three')]);
$finally = new Finally_([$echo3]);

return new TryCatch($tryStmts, [$catch]);
