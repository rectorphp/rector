<?php

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

$classMethod = new ClassMethod('methodName');
$classMethod->flags = Class_::MODIFIER_PUBLIC;

return $classMethod;
