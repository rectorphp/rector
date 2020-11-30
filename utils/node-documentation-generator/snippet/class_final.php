<?php

declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;

$class = new Class_('ClassName');
$class->flags |= Class_::MODIFIER_FINAL;

return $class;
