<?php

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

return new ClassMethod('methodName', ['flags' => Class_::MODIFIER_PUBLIC]);
