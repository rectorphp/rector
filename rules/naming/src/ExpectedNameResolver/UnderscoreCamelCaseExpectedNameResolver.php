<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Core\Util\StaticRectorStrings;

final class UnderscoreCamelCaseExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolve(Property $property): string
    {
        $currentName = $this->nodeNameResolver->getName($property);

        return StaticRectorStrings::underscoreToCamelCase($currentName);
    }
}
