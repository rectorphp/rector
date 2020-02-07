<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;

interface PropertyTypeInfererInterface extends PriorityAwareTypeInfererInterface
{
    public function inferProperty(Property $property): Type;
}
