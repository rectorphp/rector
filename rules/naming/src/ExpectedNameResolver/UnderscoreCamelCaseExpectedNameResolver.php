<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Core\Util\StaticRectorStrings;

class UnderscoreCamelCaseExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolve(Property $property): string
    {
        return $this->resolveIfNotYet($property);
    }

    public function resolveIfNotYet(Property $property): string
    {
        $currentName = $this->nodeNameResolver->getName($property);

        return StaticRectorStrings::underscoreToCamelCase($currentName);
    }
}
