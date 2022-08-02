<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class ScopeFactory
{
    /**
     * @readonly
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;
    public function __construct(PHPStanScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }
    public function createFromFile(SmartFileInfo $fileInfo) : MutatingScope
    {
        $scopeContext = ScopeContext::create($fileInfo->getRealPath());
        return $this->phpStanScopeFactory->create($scopeContext);
    }
}
