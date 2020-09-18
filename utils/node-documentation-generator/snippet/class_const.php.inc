<?php

use PhpParser\Node\Const_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;

$const = new Const_('SOME_CLASS_CONSTANT', new String_('default value'));

return new ClassConst([$const], Class_::MODIFIER_PUBLIC);
