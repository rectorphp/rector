<?php

declare(strict_types=1);

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Echo_;

$string = new String_('hello');

return new Echo_([$string]);
