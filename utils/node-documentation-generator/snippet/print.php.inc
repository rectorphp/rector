<?php

use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Scalar\String_;

$string = new String_('Some php code');

return new Print_($string);
