<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver;

final class BoolPropertyConflictingNameGuard extends AbstractPropertyConflictingNameGuard
{
    /**
     * @required
     */
    public function autowireBoolPropertyConflictingNameGuard(
        BoolPropertyExpectedNameResolver $boolPropertyExpectedNameResolver
    ): void {
        $this->expectedNameResolver = $boolPropertyExpectedNameResolver;
    }
}
