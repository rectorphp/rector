<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Util\StaticRectorStrings;

final class UnderscoreCamelCaseExpectedNameResolver extends AbstractExpectedNameResolver
{
    /**
     * @param Param|Property $node
     */
    public function resolve(Node $node): ?string
    {
        $currentName = $this->nodeNameResolver->getName($node);
        if ($currentName === null) {
            return null;
        }

        return StaticRectorStrings::underscoreToCamelCase($currentName);
    }
}
