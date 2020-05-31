<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ScopeFactory
{
    /**
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;

    public function __construct(PHPStanScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }

    public function createFromFile(SmartFileInfo $fileInfo): Scope
    {
        return $this->phpStanScopeFactory->create(ScopeContext::create($fileInfo->getRealPath()));
    }
}
