<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;

final class MatchTypePropertyRenamer extends AbstractPropertyRenamer
{
    public function __construct(MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard)
    {
        $this->conflictingPropertyNameGuard = $matchPropertyTypeConflictingNameGuard;
    }
}
