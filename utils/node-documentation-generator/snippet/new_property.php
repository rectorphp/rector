<?php

declare(strict_types=1);

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

$propertyProperty = new PropertyProperty(new Identifier('propertyName'));

return new Property(Class_::MODIFIER_PUBLIC, [$propertyProperty]);
