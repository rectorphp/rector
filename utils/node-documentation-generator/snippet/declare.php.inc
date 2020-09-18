<?php

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;

$declareDeclare = new DeclareDeclare('strict_types', new LNumber(1));

return new Declare_([$declareDeclare]);

