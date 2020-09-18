<?php

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Echo_;

$msg = new String_('hello');

return new Echo_([$msg]);
