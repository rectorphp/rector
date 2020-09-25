<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use Rector\Naming\Guard\PropertyConflictingNameGuard\BoolPropertyConflictingNameGuard;

final class BoolPropertyRenamer extends AbstractPropertyRenamer
{
    public function __construct(BoolPropertyConflictingNameGuard $boolPropertyConflictingNameGuard)
    {
        $this->conflictingPropertyNameGuard = $boolPropertyConflictingNameGuard;
    }
}
