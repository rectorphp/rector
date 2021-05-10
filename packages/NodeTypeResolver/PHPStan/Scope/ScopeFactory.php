<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ScopeFactory
{
    public function __construct(
        private PHPStanScopeFactory $phpStanScopeFactory
    ) {
    }

    public function createFromFile(SmartFileInfo $fileInfo): MutatingScope
    {
        $scopeContext = ScopeContext::create($fileInfo->getRealPath());
        return $this->phpStanScopeFactory->create($scopeContext);
    }
}
