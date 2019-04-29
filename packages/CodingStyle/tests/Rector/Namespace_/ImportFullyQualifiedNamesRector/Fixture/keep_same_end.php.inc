<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Some\Trait_;

final class SameEnd
{
    /**
     * @param Another\Trait_ $phpStanScopeFactory
     */
    public function __construct(Another\Trait_ $phpStanScopeFactory)
    {
    }
}
