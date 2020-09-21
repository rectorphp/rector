<?php

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Case_;

$trueConstFetch = new ConstFetch(new Name('true'));

return new Case_($trueConstFetch);
