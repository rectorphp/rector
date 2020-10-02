<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use Rector\Naming\ExpectedNameResolver\UnderscoreCamelCaseExpectedNameResolver;

final class UnderscoreCamelCaseConflictingNameGuard extends AbstractPropertyConflictingNameGuard
{
    /**
     * @required
     */
    public function autowireUnderscoreCamelCaseConflictingNameGuard(
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver
    ): void {
        $this->expectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
    }
}
