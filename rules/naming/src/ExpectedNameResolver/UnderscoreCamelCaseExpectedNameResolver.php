<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use Rector\Core\Util\StaticRectorStrings;

final class UnderscoreCamelCaseExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolve(Node $node): ?string
    {
        $currentName = $this->nodeNameResolver->getName($node);
        if ($currentName === null) {
            return null;
        }

        return StaticRectorStrings::underscoreToCamelCase($currentName);
    }
}
