<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Scalar\String_;

$string = new String_('some string');

return new Throw_($string);
