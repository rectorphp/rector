<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use Rector\Naming\Guard\PropertyConflictingNameGuard\UnderscoreCamelCaseConflictingNameGuard;

class UnderscoreCamelCasePropertyRenamer extends AbstractPropertyRenamer
{
    public function __construct(UnderscoreCamelCaseConflictingNameGuard $underscoreCamelCaseConflictingNameGuard)
    {
        $this->conflictingPropertyNameGuard = $underscoreCamelCaseConflictingNameGuard;
    }
}
