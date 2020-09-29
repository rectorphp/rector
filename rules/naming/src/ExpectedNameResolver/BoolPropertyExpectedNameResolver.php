<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;

final class BoolPropertyExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolve(Property $property): ?string
    {
        if ($this->nodeTypeResolver->isPropertyBoolean($property)) {
            return $this->propertyNaming->getExpectedNameFromBooleanPropertyType($property);
        }

        return null;
    }
}
