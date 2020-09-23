<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Core\Util\StaticRectorStrings;

class UnderscoreToCamelCaseExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolve(Property $property): void
    {
    }

    public function resolveIfNotYet(Property $property)
    {
        $currentName = $this->nodeNameResolver->getName($property);

        $camelCaseName = StaticRectorStrings::underscoreToCamelCase($currentName);
        if ($property->isStatic()) {
            $camelCaseName = '$' . $camelCaseName;
        }

        return $camelCaseName;
    }
}
