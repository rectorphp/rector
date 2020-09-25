<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;

class MatchPropertyTypeConflictingNameGuard extends AbstractPropertyConflictingNameGuard
{
    /**
     * @required
     */
    public function autowireMatchPropertyTypePropertyConflictingNameGuard(
        MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver
    ): void {
        $this->expectedNameResolver = $matchPropertyTypeExpectedNameResolver;
    }
}
