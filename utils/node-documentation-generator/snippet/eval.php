<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Scalar\String_;

$string = new String_('Some php code');

return new Eval_(new String_('Some php code'));
