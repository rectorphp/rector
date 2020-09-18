<?php

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\DeclareDeclare;

return new DeclareDeclare('strict_types', new LNumber(1));
