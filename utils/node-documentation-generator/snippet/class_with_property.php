<?php

declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

$class = new Class_('ClassName');

$propertyProperty = new PropertyProperty('someProperty');
$property = new Property(Class_::MODIFIER_PRIVATE, [$propertyProperty]);

$class->stmts[] = $property;

return $propertyProperty;
