<?php

declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;

$propertyProperty = new PropertyProperty(new VarLikeIdentifier('propertyName'));

return new Property(Class_::MODIFIER_PUBLIC, [$propertyProperty]);
