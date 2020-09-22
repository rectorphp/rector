<?php

declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

$propertyProperties = [new PropertyProperty('firstProperty'), new PropertyProperty('secondProperty')];

return new Property(Class_::MODIFIER_STATIC | Class_::MODIFIER_PUBLIC, $propertyProperties);
