<?php

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;

$class = new Class_(null);

return new New_($class);
