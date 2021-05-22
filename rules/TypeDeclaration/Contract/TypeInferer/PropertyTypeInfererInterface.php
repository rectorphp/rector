<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;

interface PropertyTypeInfererInterface extends PriorityAwareTypeInfererInterface
{
    /**
     * Return null if no type can be inferred.
     * Return MixedType if unknown type is inferred.
     */
    public function inferProperty(Property $property): ?Type;
}
