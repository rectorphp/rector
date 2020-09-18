<?php

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;

$class = new Class_('ClassName');

$class->flags = Class_::MODIFIER_FINAL;
$class->extends = new Identifier('Parent');

return $class;
