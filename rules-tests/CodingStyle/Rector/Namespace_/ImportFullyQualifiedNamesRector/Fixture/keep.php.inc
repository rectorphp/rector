<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;

final class ScopeFactory
{
    /**
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;

    /**
     * @param PHPStanScopeFactory $phpStanScopeFactory
     */
    public function __construct(PHPStanScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }
}
