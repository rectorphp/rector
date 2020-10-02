<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;

final class MatchPropertyTypeConflictingNameGuard extends AbstractPropertyConflictingNameGuard
{
    /**
     * @required
     */
    public function autowireMatchPropertyTypeConflictingNameGuard(
        MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver
    ): void {
        $this->expectedNameResolver = $matchPropertyTypeExpectedNameResolver;
    }
}
