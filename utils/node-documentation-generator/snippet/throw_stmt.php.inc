<?php

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Throw_;

$string = new String_('some string');

return new Throw_($string);
