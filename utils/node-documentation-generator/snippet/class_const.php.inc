<?php

use PhpParser\Node\Const_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;

$defaultValue = new String_('default value');
$const = new Const_('SOME_CLASS_CONSTANT', $defaultValue);

return new ClassConst([$const], Class_::MODIFIER_PUBLIC);
